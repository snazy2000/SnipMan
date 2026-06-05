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
        // snippets: composite owner index (all WHERE owner_type + owner_id queries)
        Schema::table('snippets', function (Blueprint $table) {
            $table->index(['owner_type', 'owner_id'], 'snippets_owner_composite_index');
        });

        // folders: composite owner index
        Schema::table('folders', function (Blueprint $table) {
            $table->index(['owner_type', 'owner_id'], 'folders_owner_composite_index');
        });

        // team_user: team-side of pivot + invitation lookup columns
        Schema::table('team_user', function (Blueprint $table) {
            $table->index(['team_id'], 'team_user_team_id_index');
            $table->index(['invitation_token'], 'team_user_invitation_token_index');
            $table->index(['invitation_status'], 'team_user_invitation_status_index');
        });

        // snippet_versions: optimise version-number sort
        Schema::table('snippet_versions', function (Blueprint $table) {
            $table->index(['snippet_id', 'version_number'], 'snippet_versions_id_version_index');
        });
    }

    public function down(): void
    {
        Schema::table('snippets', function (Blueprint $table) {
            $table->dropIndex('snippets_owner_composite_index');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex('folders_owner_composite_index');
        });

        Schema::table('team_user', function (Blueprint $table) {
            $table->dropIndex('team_user_team_id_index');
            $table->dropIndex('team_user_invitation_token_index');
            $table->dropIndex('team_user_invitation_status_index');
        });

        Schema::table('snippet_versions', function (Blueprint $table) {
            $table->dropIndex('snippet_versions_id_version_index');
        });
    }
};
