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
        Schema::table('team_user', function (Blueprint $table) {
            $table->string('invitation_status')->default('accepted')->after('role'); // accepted, pending
            $table->string('invitation_token')->nullable()->after('invitation_status');
            $table->timestamp('invited_at')->nullable()->after('invitation_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropColumn(['invitation_status', 'invitation_token', 'invited_at']);
        });
    }
};
