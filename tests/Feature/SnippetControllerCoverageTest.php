<?php

use App\Models\Folder;
use App\Models\Snippet;
use App\Models\SnippetShare;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Create method tests
test('user can view snippet create form', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'editor']);

    $response = $this->actingAs($user)->get(route('snippets.create'));

    $response->assertOk()
        ->assertViewIs('snippets.create')
        ->assertViewHas('teams')
        ->assertViewHas('personalFolders')
        ->assertViewHas('teamFolders');
});

test('snippet create form loads personal and team folders', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'owner']);

    $personalFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Personal Folder']);
    $teamFolder = Folder::factory()->for($team, 'owner')->create(['name' => 'Team Folder']);

    $response = $this->actingAs($user)->get(route('snippets.create'));

    $response->assertOk();

    $personalFolders = $response->viewData('personalFolders');
    $teamFolders = $response->viewData('teamFolders');

    expect($personalFolders)->toHaveCount(1);
    expect($teamFolders)->toHaveCount(1);
});

// Edit method tests
test('user can view snippet edit form', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->get(route('snippets.edit', $snippet));

    $response->assertOk()
        ->assertViewIs('snippets.edit')
        ->assertViewHas('snippet')
        ->assertViewHas('personalFolders')
        ->assertViewHas('teamFolders');
});

test('user cannot view edit form for other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($otherUser, 'owner')->create();

    $response = $this->actingAs($user)->get(route('snippets.edit', $snippet));

    $response->assertForbidden();
});

test('team editor can view edit form for team snippet', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'editor']);
    $snippet = Snippet::factory()->for($team, 'owner')->create();

    $response = $this->actingAs($user)->get(route('snippets.edit', $snippet));

    $response->assertOk()
        ->assertViewIs('snippets.edit');
});

// getShareStatus method tests
test('user can get share status for their snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->get(route('snippets.shareStatus', $snippet));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'shared' => false,
            'share_url' => null,
            'uuid' => null,
        ]);
});

test('user can get share status for shared snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();
    $share = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => true,
        'views' => 0,
    ]);

    $response = $this->actingAs($user)->get(route('snippets.shareStatus', $snippet));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'shared' => true,
        ])
        ->assertJsonStructure([
            'success',
            'shared',
            'share_url',
            'uuid',
        ]);
});

test('user cannot get share status for other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($otherUser, 'owner')->create();

    $response = $this->actingAs($user)->get(route('snippets.shareStatus', $snippet));

    $response->assertForbidden();
});

// toggleShare method tests
test('user can toggle share on for their snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->post(route('snippets.toggleShare', $snippet));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'shared' => true,
        ])
        ->assertJsonStructure([
            'success',
            'shared',
            'share_url',
            'uuid',
        ]);

    expect($snippet->activeShares()->count())->toBe(1);
});

test('user can toggle share off for their snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();
    $share = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => true,
        'views' => 0,
    ]);

    $response = $this->actingAs($user)->post(route('snippets.toggleShare', $snippet));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'shared' => false,
            'message' => 'Public sharing disabled',
        ]);

    expect($snippet->activeShares()->count())->toBe(0);
});

test('user cannot toggle share for other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($otherUser, 'owner')->create();

    $response = $this->actingAs($user)->post(route('snippets.toggleShare', $snippet));

    $response->assertForbidden();
});

// Move method additional coverage
test('user can move snippet to valid folder in same context', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();
    $folder = Folder::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->postJson(route('snippets.move', $snippet), [
        'folder_id' => $folder->id,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    expect($snippet->fresh()->folder_id)->toBe($folder->id);
});

test('user can move snippet to root by setting folder_id to null', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user, 'owner')->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create(['folder_id' => $folder->id]);

    $response = $this->actingAs($user)->postJson(route('snippets.move', $snippet), [
        'folder_id' => null,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    expect($snippet->fresh()->folder_id)->toBeNull();
});

test('user cannot move snippet to folder belonging to different owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();
    $otherFolder = Folder::factory()->for($otherUser, 'owner')->create();

    $response = $this->actingAs($user)->postJson(route('snippets.move', $snippet), [
        'folder_id' => $otherFolder->id,
    ]);

    $response->assertStatus(403);
});

test('team member can move team snippet to team folder', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'editor']);

    $snippet = Snippet::factory()->for($team, 'owner')->create();
    $folder = Folder::factory()->for($team, 'owner')->create();

    $response = $this->actingAs($user)->postJson(route('snippets.move', $snippet), [
        'folder_id' => $folder->id,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    expect($snippet->fresh()->folder_id)->toBe($folder->id);
});

test('user can move personal snippet to team folder if they are member', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'editor']);

    $snippet = Snippet::factory()->for($user, 'owner')->create();
    $teamFolder = Folder::factory()->for($team, 'owner')->create();

    $response = $this->actingAs($user)->postJson(route('snippets.move', $snippet), [
        'folder_id' => $teamFolder->id,
    ]);

    // Moving personal snippet to team folder should be allowed if user has folder access
    $response->assertOk();
});

// Clone method additional coverage
test('user can clone snippet to personal ownership', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create([
        'title' => 'Original Snippet',
        'language' => 'javascript',
        'content' => 'console.log("test");',
    ]);

    $response = $this->actingAs($user)->post(route('snippets.clone', $snippet), [
        'title' => 'Cloned Snippet',
        'owner_type' => 'personal',
        'folder_id' => null,
    ]);

    $response->assertRedirect();

    $cloned = Snippet::where('title', 'Cloned Snippet')->first();
    expect($cloned)->not->toBeNull();
    expect($cloned->language)->toBe($snippet->language);
    expect($cloned->content)->toBe($snippet->content);
    expect($cloned->owner_type)->toBe('App\Models\User');
    expect($cloned->owner_id)->toBe($user->id);
});

test('user can clone snippet to team ownership', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'owner']);

    $snippet = Snippet::factory()->for($user, 'owner')->create([
        'title' => 'Original Snippet',
        'language' => 'python',
        'content' => 'print("test")',
    ]);

    $response = $this->actingAs($user)->post(route('snippets.clone', $snippet), [
        'title' => 'Team Cloned Snippet',
        'owner_type' => 'team',
        'team_id' => $team->id,
        'folder_id' => null,
    ]);

    $response->assertRedirect();

    $cloned = Snippet::where('title', 'Team Cloned Snippet')->first();
    expect($cloned)->not->toBeNull();
    expect($cloned->owner_type)->toBe('App\Models\Team');
    expect($cloned->owner_id)->toBe($team->id);
});

test('user cannot clone snippet to team without proper permissions', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'viewer']);

    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->post(route('snippets.clone', $snippet), [
        'title' => 'Cloned Snippet',
        'owner_type' => 'team',
        'team_id' => $team->id,
    ]);

    $response->assertStatus(403);
});

test('user can clone snippet with tags', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create([
        'title' => 'Tagged Snippet',
        'user_tags' => ['tag1', 'tag2', 'tag3'],
    ]);

    $response = $this->actingAs($user)->post(route('snippets.clone', $snippet), [
        'title' => 'Cloned Tagged Snippet',
        'owner_type' => 'personal',
    ]);

    $response->assertRedirect();

    $cloned = Snippet::where('title', 'Cloned Tagged Snippet')->first();
    expect($cloned->user_tags)->toBe($snippet->user_tags);
});

// Update method additional coverage
test('updating snippet with same content does not create new version', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create([
        'content' => 'Original content',
    ]);

    // Create initial version
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Original content',
        'created_by' => $user->id,
    ]);

    $versionCountBefore = $snippet->versions()->count();

    $response = $this->actingAs($user)->patch(route('snippets.update', $snippet), [
        'title' => $snippet->title,
        'language' => $snippet->language,
        'content' => 'Original content', // Same content
    ]);

    $response->assertRedirect();

    expect($snippet->versions()->count())->toBe($versionCountBefore);
});

test('updating snippet with different content creates new version', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create([
        'content' => 'Version 1 content',
    ]);

    // Create initial version
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Version 1 content',
        'created_by' => $user->id,
    ]);

    $versionCountBefore = $snippet->versions()->count();

    $response = $this->actingAs($user)->patch(route('snippets.update', $snippet), [
        'title' => $snippet->title,
        'language' => $snippet->language,
        'content' => 'Version 2 content', // Different content
    ]);

    $response->assertRedirect();

    expect($snippet->versions()->count())->toBe($versionCountBefore + 1);

    $latestVersion = $snippet->versions()->latest('version_number')->first();
    expect($latestVersion->content)->toBe('Version 2 content');
});

// Destroy method coverage
test('deleting snippet also deletes its versions', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    // Create multiple versions
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Version 1',
        'created_by' => $user->id,
    ]);
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 2,
        'content' => 'Version 2',
        'created_by' => $user->id,
    ]);

    expect($snippet->versions()->count())->toBe(2);

    $snippetId = $snippet->id;

    $response = $this->actingAs($user)->delete(route('snippets.destroy', $snippet));

    $response->assertRedirect();

    expect(Snippet::find($snippetId))->toBeNull();
    expect(\App\Models\SnippetVersion::where('snippet_id', $snippetId)->count())->toBe(0);
});
