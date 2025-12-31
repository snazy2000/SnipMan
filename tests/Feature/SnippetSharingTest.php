<?php

use App\Models\Snippet;
use App\Models\SnippetShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create share for snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.createShare', $snippet));

    $response->assertJson(['success' => true]);
    $response->assertJsonStructure(['share_url', 'uuid']);

    $this->assertDatabaseHas('snippet_shares', [
        'snippet_id' => $snippet->id,
        'is_active' => true,
    ]);
});

test('share returns existing share if already exists', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $existingShare = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.createShare', $snippet));

    $response->assertJson([
        'success' => true,
        'uuid' => $existingShare->uuid,
    ]);

    // Should still be only one share
    $this->assertEquals(1, $snippet->shares()->count());
});

test('user can toggle share status', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $share = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.toggleShare', $snippet));

    $response->assertJson([
        'success' => true,
        'shared' => false,
    ]);

    $share->refresh();
    $this->assertFalse($share->is_active);
});

test('user can revoke share', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $share = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('snippets.revokeShare', $snippet));

    $response->assertJson(['success' => true]);

    $share->refresh();
    $this->assertFalse($share->is_active);
});

test('guest can view shared snippet', function () {
    $snippet = Snippet::factory()->create();
    $share = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => true,
    ]);

    $response = $this->get(route('snippets.shared', $share->uuid));

    $response->assertOk();
    $response->assertSee($snippet->title);
    $response->assertSee($snippet->content);
});

test('viewing shared snippet increments view count', function () {
    $snippet = Snippet::factory()->create();
    $share = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => true,
        'views' => 0,
    ]);

    $this->get(route('snippets.shared', $share->uuid));

    $share->refresh();
    $this->assertEquals(1, $share->views);
    $this->assertNotNull($share->last_viewed_at);
});

test('cannot view inactive shared snippet', function () {
    $snippet = Snippet::factory()->create();
    $share = SnippetShare::create([
        'snippet_id' => $snippet->id,
        'is_active' => false,
    ]);

    $response = $this->get(route('snippets.shared', $share->uuid));

    $response->assertNotFound();
});

test('cannot view shared snippet with invalid uuid', function () {
    $response = $this->get(route('snippets.shared', 'invalid-uuid'));

    $response->assertNotFound();
});

test('user can see list of their shared snippets', function () {
    $user = User::factory()->create();

    $snippet1 = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'title' => 'Shared Snippet 1',
    ]);

    $snippet2 = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'title' => 'Shared Snippet 2',
    ]);

    SnippetShare::create([
        'snippet_id' => $snippet1->id,
        'is_active' => true,
    ]);

    SnippetShare::create([
        'snippet_id' => $snippet2->id,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('snippets.sharedList'));

    $response->assertOk();
    $response->assertSee('Shared Snippet 1');
    $response->assertSee('Shared Snippet 2');
});

test('user cannot create share for other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $otherUser->id,
        'created_by' => $otherUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('snippets.createShare', $snippet));

    $response->assertForbidden();
});
