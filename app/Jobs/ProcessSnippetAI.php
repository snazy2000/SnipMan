<?php

namespace App\Jobs;

use App\Models\Snippet;
use App\Services\AIService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSnippetAI implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public int $backoff = 60; // seconds

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Snippet $snippet,
        public bool $forceReprocess = false
    ) {
        $this->onQueue(config('ai.processing.queue', 'default'));
    }

    /**
     * Execute the job.
     */
    public function handle(AIService $aiService): void
    {
        try {
            Log::info('Starting AI analysis job processing', [
                'snippet_id' => $this->snippet->id,
                'snippet_title' => $this->snippet->title,
                'language' => $this->snippet->language,
                'content_length' => strlen($this->snippet->content),
                'force_reprocess' => $this->forceReprocess,
                'current_provider' => $aiService->getProviderName(),
                'attempts' => $this->attempts(),
                'queue' => config('ai.processing.queue', 'default'),
                'current_ai_status' => [
                    'ai_processed_at' => $this->snippet->ai_processed_at,
                    'ai_processing_failed' => $this->snippet->ai_processing_failed,
                    'has_description' => ! empty($this->snippet->ai_description),
                ],
            ]);

            // Skip if already processed and not forcing reprocess
            if (! $this->forceReprocess && $this->snippet->ai_processed_at && ! $this->snippet->ai_processing_failed) {
                Log::info('Snippet already processed, skipping', [
                    'snippet_id' => $this->snippet->id,
                    'ai_processed_at' => $this->snippet->ai_processed_at,
                ]);

                return;
            }

            // Check if AI service is available
            Log::info('Checking AI service availability', [
                'snippet_id' => $this->snippet->id,
                'provider' => $aiService->getProviderName(),
                'provider_config' => $aiService->getProviderConfig(),
            ]);

            if (! $aiService->isAvailable()) {
                Log::warning('AI service not available', [
                    'snippet_id' => $this->snippet->id,
                    'provider' => $aiService->getProviderName(),
                ]);
                $this->markAsFailed('AI service not available');

                return;
            }

            Log::info('AI service is available, starting code analysis', [
                'snippet_id' => $this->snippet->id,
                'provider' => $aiService->getProviderName(),
            ]);

            // Analyze the code
            $startTime = microtime(true);
            $results = $aiService->analyzeCode($this->snippet->content, $this->snippet->language);
            $processingTime = microtime(true) - $startTime;

            Log::info('AI analysis completed', [
                'snippet_id' => $this->snippet->id,
                'processing_time_seconds' => round($processingTime, 2),
                'results_summary' => [
                    'has_description' => ! empty($results['description']),
                    'description_length' => ! empty($results['description']) ? strlen($results['description']) : 0,
                    'processed_at' => $results['processed_at'] ?? null,
                ],
            ]);

            // Update the snippet with AI results
            $this->snippet->update([
                'ai_description' => $results['description'],
                'ai_processed_at' => $results['processed_at'],
                'ai_processing_failed' => false,
            ]);

            Log::info('Snippet updated with AI results successfully', [
                'snippet_id' => $this->snippet->id,
                'has_description' => ! empty($results['description']),
            ]);

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $isRateLimited = $errorCode === 429 || str_contains($errorMessage, '429') || str_contains($errorMessage, 'rate-limited') || str_contains($errorMessage, 'rate limit');

            Log::error('AI processing failed with exception', [
                'snippet_id' => $this->snippet->id,
                'error_message' => $errorMessage,
                'error_code' => $errorCode,
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'attempts' => $this->attempts(),
                'provider' => $aiService ? $aiService->getProviderName() : 'unknown',
                'is_rate_limited' => $isRateLimited,
                'trace' => $e->getTraceAsString(),
            ]);

            // For rate limiting, provide specific guidance
            if ($isRateLimited && $aiService && method_exists($aiService->getProvider(), 'getRateLimitAdvice')) {
                $advice = $aiService->getProvider()->getRateLimitAdvice();
                Log::info('Rate limit advice for snippet processing', [
                    'snippet_id' => $this->snippet->id,
                    'advice' => $advice,
                ]);

                // Set a more helpful error message for rate limiting
                $helpfulMessage = 'Rate limited by AI provider. ';
                if (isset($advice['is_free_model']) && $advice['is_free_model']) {
                    $helpfulMessage .= 'Consider switching to a paid model (like anthropic/claude-3.5-sonnet) or adding your own API key for better reliability.';
                } else {
                    $helpfulMessage .= 'Please wait a moment and try again.';
                }

                $this->markAsFailed($helpfulMessage);
            } else {
                $this->markAsFailed($errorMessage);
            }

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('AI processing job failed permanently', [
            'snippet_id' => $this->snippet->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        $this->markAsFailed($exception->getMessage());
    }

    /**
     * Mark snippet as failed processing
     */
    private function markAsFailed(string $reason): void
    {
        $this->snippet->update([
            'ai_processing_failed' => true,
            'ai_processed_at' => now(),
        ]);

        Log::warning('Marked snippet as AI processing failed', [
            'snippet_id' => $this->snippet->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 120, 300]; // 1 min, 2 min, 5 min
    }
}
