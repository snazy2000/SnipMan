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
            // Drop the existing foreign key constraint
            $table->dropForeign(['folder_id']);

            // Modify the column to be nullable
            $table->foreignId('folder_id')->nullable()->change();

            // Re-add the foreign key constraint with nullable support
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('snippets', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['folder_id']);

            // Make the column not nullable again
            $table->foreignId('folder_id')->nullable(false)->change();

            // Re-add the original foreign key constraint
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }
};
