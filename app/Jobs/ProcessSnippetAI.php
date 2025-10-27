<?php

namespace App\Jobs;

use App\Models\Snippet;
use App\Services\LocalAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessSnippetAI implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

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
    public function handle(LocalAIService $aiService): void
    {
        try {
            Log::info('Processing AI analysis for snippet', [
                'snippet_id' => $this->snippet->id,
                'language' => $this->snippet->language,
                'force_reprocess' => $this->forceReprocess
            ]);

            // Skip if already processed and not forcing reprocess
            if (!$this->forceReprocess && $this->snippet->ai_processed_at && !$this->snippet->ai_processing_failed) {
                Log::info('Snippet already processed, skipping', ['snippet_id' => $this->snippet->id]);
                return;
            }

            // Check if AI service is available
            if (!$aiService->isAvailable()) {
                Log::warning('AI service not available', ['snippet_id' => $this->snippet->id]);
                $this->markAsFailed('AI service not available');
                return;
            }

            // Analyze the code
            $results = $aiService->analyzeCode($this->snippet->content, $this->snippet->language);

            // Update the snippet with AI results
            $this->snippet->update([
                'ai_description' => $results['description'],
                'ai_tags' => !empty($results['tags']) ? json_encode($results['tags']) : null,
                'ai_quality_score' => $results['quality_score'],
                'ai_processed_at' => $results['processed_at'],
                'ai_processing_failed' => false,
            ]);

            Log::info('AI analysis completed successfully', [
                'snippet_id' => $this->snippet->id,
                'has_description' => !empty($results['description']),
                'tags_count' => count($results['tags']),
                'quality_score' => $results['quality_score']
            ]);

        } catch (Exception $e) {
            Log::error('AI processing failed', [
                'snippet_id' => $this->snippet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->markAsFailed($e->getMessage());
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
            'attempts' => $this->attempts()
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
            'reason' => $reason
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
