<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSnippetAI;
use App\Models\Snippet;
use Illuminate\Console\Command;

class ProcessAllSnippetsAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snippets:process-ai {--force : Force reprocess all snippets} {--limit=10 : Number of snippets to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process AI analysis for snippets that haven\'t been analyzed yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        if ($force) {
            $this->info('🔄 Force processing ALL snippets...');
            $snippets = Snippet::limit($limit)->get();
        } else {
            $this->info('🤖 Processing snippets without AI analysis...');
            $snippets = Snippet::where(function ($query) {
                $query->whereNull('ai_processed_at')
                    ->orWhere('ai_processing_failed', true);
            })->limit($limit)->get();
        }

        if ($snippets->count() === 0) {
            $this->info('✅ No snippets need AI processing!');

            return Command::SUCCESS;
        }

        $this->info("📝 Found {$snippets->count()} snippets to process");

        $bar = $this->output->createProgressBar($snippets->count());
        $bar->start();

        foreach ($snippets as $snippet) {
            ProcessSnippetAI::dispatch($snippet, $force);
            $bar->advance();

            // Small delay to prevent overwhelming the queue
            usleep(100000); // 0.1 second
        }

        $bar->finish();
        $this->newLine();
        $this->info('🚀 AI processing jobs dispatched! Run `php artisan queue:work` to process them.');

        return Command::SUCCESS;
    }
}
