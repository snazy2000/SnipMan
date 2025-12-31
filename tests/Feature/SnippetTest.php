<?php

use App\Jobs\ProcessSnippetAI;
use App\Models\AISetting;
use App\Models\Folder;
use App\Models\Snippet;
use App\Models\SnippetVersion;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('user can create personal snippet', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.store'), [
            'title' => 'Test Snippet',
            'language' => 'php',
            'content' => '<?php echo "Hello World";',
            'folder_id' => $folder->id,
            'owner_type' => 'personal',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('snippets', [
        'title' => 'Test Snippet',
        'language' => 'php',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    // Check initial version created
    $snippet = Snippet::where('title', 'Test Snippet')->first();
    $this->assertDatabaseHas('snippet_versions', [
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'created_by' => $user->id,
    ]);
});

test('user can create team snippet', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $team->members()->attach($user->id, ['role' => 'editor']);

    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\Team',
        'owner_id' => $team->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.store'), [
            'title' => 'Team Snippet',
            'language' => 'javascript',
            'content' => 'console.log("Hello");',
            'folder_id' => $folder->id,
            'owner_type' => 'team',
            'team_id' => $team->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('snippets', [
        'title' => 'Team Snippet',
        'owner_type' => 'App\Models\Team',
        'owner_id' => $team->id,
    ]);
});

test('snippet creation triggers AI processing when enabled', function () {
    Queue::fake();

    // Enable AI auto description
    AISetting::create([
        'key' => 'ai.features.auto_description',
        'value' => '1',
        'label' => 'Auto Description',
    ]);

    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('snippets.store'), [
            'title' => 'Test Snippet',
            'language' => 'php',
            'content' => '<?php echo "test";',
            'folder_id' => $folder->id,
            'owner_type' => 'personal',
        ]);

    Queue::assertPushed(ProcessSnippetAI::class);
});

test('user can update snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'title' => 'Original Title',
        'content' => 'Original Content',
    ]);

    // Create initial version
    SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Original Content',
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('snippets.update', $snippet), [
            'title' => 'Updated Title',
            'language' => 'php',
            'content' => 'Updated Content',
            'folder_id' => $snippet->folder_id,
        ]);

    $response->assertRedirect(route('snippets.show', $snippet));
    $this->assertDatabaseHas('snippets', [
        'id' => $snippet->id,
        'title' => 'Updated Title',
        'content' => 'Updated Content',
    ]);

    // Check new version created
    $this->assertDatabaseHas('snippet_versions', [
        'snippet_id' => $snippet->id,
        'version_number' => 2,
        'content' => 'Updated Content',
    ]);
});

test('updating snippet without content change does not create new version', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'content' => 'Same Content',
    ]);

    SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Same Content',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->patch(route('snippets.update', $snippet), [
            'title' => 'Different Title',
            'language' => 'php',
            'content' => 'Same Content',
            'folder_id' => $snippet->folder_id,
        ]);

    $this->assertEquals(1, $snippet->versions()->count());
});

test('user can delete snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('snippets.destroy', $snippet));

    $response->assertRedirect(route('snippets.index'));
    $this->assertDatabaseMissing('snippets', ['id' => $snippet->id]);
});

test('user cannot delete other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $otherUser->id,
        'created_by' => $otherUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('snippets.destroy', $snippet));

    $response->assertForbidden();
});

test('user can clone snippet', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'title' => 'Original',
        'content' => 'Test Content',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.clone', $snippet), [
            'title' => 'Cloned Snippet',
            'owner_type' => 'personal',
            'folder_id' => $folder->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('snippets', [
        'title' => 'Cloned Snippet',
        'content' => 'Test Content',
        'owner_id' => $user->id,
    ]);
});

test('user can move snippet to different folder', function () {
    $user = User::factory()->create();
    $folder1 = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);
    $folder2 = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'folder_id' => $folder1->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.move', $snippet), [
            'folder_id' => $folder2->id,
        ]);

    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('snippets', [
        'id' => $snippet->id,
        'folder_id' => $folder2->id,
    ]);
});

test('user can add tags to snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => $snippet->content,
        'created_by' => $user->id,
    ]);

    $tags = ['php', 'laravel', 'testing'];

    $response = $this
        ->actingAs($user)
        ->patch(route('snippets.update', $snippet), [
            'title' => $snippet->title,
            'language' => $snippet->language,
            'content' => $snippet->content,
            'folder_id' => $snippet->folder_id,
            'user_tags' => json_encode($tags),
        ]);

    $response->assertRedirect();
    $snippet->refresh();
    $this->assertEquals($tags, $snippet->user_tags);
});

test('team viewer cannot update team snippet', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'viewer']);

    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\Team',
        'owner_id' => $team->id,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('snippets.update', $snippet), [
            'title' => 'Updated Title',
            'language' => 'php',
            'content' => 'Updated Content',
            'folder_id' => $snippet->folder_id,
        ]);

    $response->assertForbidden();
});

test('user cannot see other users personal snippets', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 creates personal snippet
    $snippet1 = Snippet::factory()->create([
        'title' => 'User 1 Personal Snippet',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user1->id,
        'created_by' => $user1->id,
    ]);

    // User 2 creates personal snippet
    $snippet2 = Snippet::factory()->create([
        'title' => 'User 2 Personal Snippet',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user2->id,
        'created_by' => $user2->id,
    ]);

    // User 1 should only see their own snippet
    $response = $this
        ->actingAs($user1)
        ->get(route('snippets.index'));

    $response->assertSee('User 1 Personal Snippet');
    $response->assertDontSee('User 2 Personal Snippet');

    // User 2 should only see their own snippet
    $response = $this
        ->actingAs($user2)
        ->get(route('snippets.index'));

    $response->assertSee('User 2 Personal Snippet');
    $response->assertDontSee('User 1 Personal Snippet');

    // User 2 cannot directly access User 1's snippet
    $response = $this
        ->actingAs($user2)
        ->get(route('snippets.show', $snippet1));

    $response->assertForbidden();
});

test('user can see team snippets only from their teams', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    // Create Team A with user1 and user2
    $teamA = Team::factory()->create(['owner_id' => $user1->id]);
    $teamA->members()->attach($user1->id, ['role' => 'owner', 'invitation_status' => 'accepted']);
    $teamA->members()->attach($user2->id, ['role' => 'editor', 'invitation_status' => 'accepted']);

    // Create Team B with only user3
    $teamB = Team::factory()->create(['owner_id' => $user3->id]);
    $teamB->members()->attach($user3->id, ['role' => 'owner', 'invitation_status' => 'accepted']);

    // Create snippet in Team A
    $snippetTeamA = Snippet::factory()->create([
        'title' => 'Team A Snippet',
        'owner_type' => 'App\Models\Team',
        'owner_id' => $teamA->id,
        'created_by' => $user1->id,
    ]);

    // Create snippet in Team B
    $snippetTeamB = Snippet::factory()->create([
        'title' => 'Team B Snippet',
        'owner_type' => 'App\Models\Team',
        'owner_id' => $teamB->id,
        'created_by' => $user3->id,
    ]);

    // User1 (member of Team A) can see Team A snippet but not Team B
    $response = $this
        ->actingAs($user1)
        ->get(route('snippets.index'));

    $response->assertSee('Team A Snippet');
    $response->assertDontSee('Team B Snippet');

    // User2 (member of Team A) can see Team A snippet but not Team B
    $response = $this
        ->actingAs($user2)
        ->get(route('snippets.index'));

    $response->assertSee('Team A Snippet');
    $response->assertDontSee('Team B Snippet');

    // User3 (member of Team B only) cannot see Team A snippet
    $response = $this
        ->actingAs($user3)
        ->get(route('snippets.index'));

    $response->assertDontSee('Team A Snippet');
    $response->assertSee('Team B Snippet');

    // User3 cannot directly access Team A's snippet
    $response = $this
        ->actingAs($user3)
        ->get(route('snippets.show', $snippetTeamA));

    $response->assertForbidden();
});
