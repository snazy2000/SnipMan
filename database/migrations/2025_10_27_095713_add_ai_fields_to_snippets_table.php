<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('snippets', function (Blueprint $table) {
            $table->text('ai_description')->nullable()->after('description');
            $table->json('ai_tags')->nullable()->after('ai_description');
            $table->tinyInteger('ai_quality_score')->nullable()->after('ai_tags');
            $table->timestamp('ai_processed_at')->nullable()->after('ai_quality_score');
            $table->boolean('ai_processing_failed')->default(false)->after('ai_processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('snippets', function (Blueprint $table) {
            $table->dropColumn([
                'ai_description',
                'ai_tags',
                'ai_quality_score',
                'ai_processed_at',
                'ai_processing_failed'
            ]);
        });
    }
};
