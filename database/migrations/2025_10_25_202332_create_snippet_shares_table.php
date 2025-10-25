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
        Schema::create('snippet_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snippet_id')->constrained()->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('views')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->index(['snippet_id']);
            $table->index(['uuid']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snippet_shares');
    }
};
