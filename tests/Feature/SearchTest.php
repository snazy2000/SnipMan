<?php

use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('search returns matching snippets by title', function () {
    $user = User::factory()->create();
    $snippet1 = Snippet::factory()->create([
        'title' => 'PHP Array Functions',
        'language' => 'python',
        'content' => 'Arrays and loops',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);
    $snippet2 = Snippet::factory()->create([
        'title' => 'JavaScript Functions',
        'language' => 'javascript',
        'content' => 'Functions and callbacks',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('search') . '?q=PHP');

    $response->assertOk();
    $response->assertSee('PHP Array Functions');
    $response->assertDontSee('JavaScript Functions');
});

test('search returns matching snippets by content', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'title' => 'Test Snippet',
        'content' => 'function calculateTotal() { return sum; }',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('search') . '?q=calculateTotal');

    $response->assertOk();
    $response->assertSee('Test Snippet');
});

test('search is case insensitive', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'title' => 'Laravel Helper Functions',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('search') . '?q=laravel');

    $response->assertOk();
    $response->assertSee('Laravel Helper Functions');
});

test('search only returns users own snippets and team snippets', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownSnippet = Snippet::factory()->create([
        'title' => 'My Snippet',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $otherSnippet = Snippet::factory()->create([
        'title' => 'Other Snippet',
        'owner_type' => 'App\Models\User',
        'owner_id' => $otherUser->id,
        'created_by' => $otherUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('search') . '?q=Snippet');

    $response->assertOk();
    $response->assertSee('My Snippet');
    $response->assertDontSee('Other Snippet');
});

test('autocomplete returns json results', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'title' => 'Test Autocomplete',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('search') . '?q=Test');

    $response->assertOk();
    $response->assertJsonFragment([
        'title' => 'Test Autocomplete',
    ]);
});

test('search returns empty results for non-matching query', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('search') . '?q=nonexistent');

    $response->assertOk();
    $response->assertJson([
        'snippets' => [],
        'folders' => [],
    ]);
});
