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
        Schema::create('snippet_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snippet_id')->constrained()->onDelete('cascade');
            $table->integer('version_number');
            $table->longText('content');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['snippet_id', 'version_number']);
            $table->index(['snippet_id']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snippet_versions');
    }
};
