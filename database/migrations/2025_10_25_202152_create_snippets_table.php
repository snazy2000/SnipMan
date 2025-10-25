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
        Schema::create('snippets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            $table->morphs('owner'); // owner_id, owner_type (User or Team) - automatically creates index
            $table->string('title');
            $table->string('language', 50)->default('text');
            $table->longText('content');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['folder_id']);
            $table->index(['created_by']);
            $table->index(['language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snippets');
    }
};
