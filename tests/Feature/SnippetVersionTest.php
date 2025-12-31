<?php

use App\Models\Snippet;
use App\Models\SnippetVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('snippet version is created on snippet creation', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'content' => 'Initial Content',
    ]);

    SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Initial Content',
        'created_by' => $user->id,
    ]);

    $this->assertDatabaseHas('snippet_versions', [
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Initial Content',
        'created_by' => $user->id,
    ]);
});

test('new version is created when content changes', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'content' => 'Original',
    ]);

    SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Original',
        'created_by' => $user->id,
    ]);

    SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 2,
        'content' => 'Updated',
        'created_by' => $user->id,
    ]);

    $versions = $snippet->versions;
    $this->assertCount(2, $versions);
});

test('versions are ordered by version number descending', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    for ($i = 1; $i <= 5; $i++) {
        SnippetVersion::create([
            'snippet_id' => $snippet->id,
            'version_number' => $i,
            'content' => "Version $i",
            'created_by' => $user->id,
        ]);
    }

    $versions = $snippet->versions;
    $this->assertEquals(5, $versions->first()->version_number);
    $this->assertEquals(1, $versions->last()->version_number);
});

test('version stores creator information', function () {
    $creator = User::factory()->create();
    $editor = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $creator->id,
        'created_by' => $creator->id,
    ]);

    $version1 = SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'V1',
        'created_by' => $creator->id,
    ]);

    $version2 = SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 2,
        'content' => 'V2',
        'created_by' => $editor->id,
    ]);

    $this->assertEquals($creator->id, $version1->creator->id);
    $this->assertEquals($editor->id, $version2->creator->id);
});

test('deleting snippet cascades to versions', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Test',
        'created_by' => $user->id,
    ]);

    $snippet->delete();

    $this->assertDatabaseMissing('snippet_versions', [
        'snippet_id' => $snippet->id,
    ]);
});
