<?php

use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Language filter ───────────────────────────────────────────────────────

test('search can filter by language', function () {
    $user = User::factory()->create();

    Snippet::factory()->create([
        'title' => 'PHP Snippet', 'language' => 'php',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);
    Snippet::factory()->create([
        'title' => 'JS Snippet', 'language' => 'javascript',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('search').'?q=Snippet&language=php');

    $response->assertOk();
    $snippets = $response->json('snippets');
    expect(collect($snippets)->pluck('title'))->toContain('PHP Snippet')
        ->and(collect($snippets)->pluck('title'))->not->toContain('JS Snippet');
});

test('language filter is case insensitive', function () {
    $user = User::factory()->create();

    Snippet::factory()->create([
        'title' => 'PHP Snippet', 'language' => 'php',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('search').'?q=Snippet&language=PHP');

    $snippets = $response->json('snippets');
    expect(collect($snippets)->pluck('title'))->toContain('PHP Snippet');
});

// ── Owner filter ──────────────────────────────────────────────────────────

test('search can filter to personal snippets only', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $team->members()->attach($user, ['role' => 'owner', 'invitation_status' => 'accepted']);

    Snippet::factory()->create([
        'title' => 'Personal Snippet',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);
    Snippet::factory()->create([
        'title' => 'Team Snippet',
        'owner_type' => 'App\Models\Team', 'owner_id' => $team->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('search').'?q=Snippet&owner=personal');

    $snippets = $response->json('snippets');
    expect(collect($snippets)->pluck('title'))->toContain('Personal Snippet')
        ->and(collect($snippets)->pluck('title'))->not->toContain('Team Snippet');
});

test('search can filter to team snippets only', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $team->members()->attach($user, ['role' => 'owner', 'invitation_status' => 'accepted']);

    Snippet::factory()->create([
        'title' => 'Personal',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);
    Snippet::factory()->create([
        'title' => 'Team Snippet',
        'owner_type' => 'App\Models\Team', 'owner_id' => $team->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('search').'?q=Snippet&owner=team');

    $snippets = $response->json('snippets');
    expect(collect($snippets)->pluck('title'))->toContain('Team Snippet')
        ->and(collect($snippets)->pluck('title'))->not->toContain('Personal');
});

// ── Tag filter ────────────────────────────────────────────────────────────

test('search can filter by tag', function () {
    $user = User::factory()->create();

    Snippet::factory()->create([
        'title' => 'Tagged', 'user_tags' => ['laravel', 'api'],
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);
    Snippet::factory()->create([
        'title' => 'Untagged', 'user_tags' => [],
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('search').'?q=ged&tag=laravel');

    $snippets = $response->json('snippets');
    expect(collect($snippets)->pluck('title'))->toContain('Tagged')
        ->and(collect($snippets)->pluck('title'))->not->toContain('Untagged');
});

// ── Date filter ───────────────────────────────────────────────────────────

test('search can filter by date_from', function () {
    $user = User::factory()->create();

    $old = Snippet::factory()->create([
        'title' => 'Old Snippet',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
        'created_at' => now()->subDays(10),
    ]);
    $new = Snippet::factory()->create([
        'title' => 'New Snippet',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(
        route('search').'?q=Snippet&date_from='.now()->subDays(1)->toDateString()
    );

    $snippets = $response->json('snippets');
    expect(collect($snippets)->pluck('title'))->toContain('New Snippet')
        ->and(collect($snippets)->pluck('title'))->not->toContain('Old Snippet');
});

test('search can filter by date_to', function () {
    $user = User::factory()->create();

    Snippet::factory()->create([
        'title' => 'Old Snippet',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
        'created_at' => now()->subDays(10),
    ]);
    Snippet::factory()->create([
        'title' => 'New Snippet',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(
        route('search').'?q=Snippet&date_to='.now()->subDays(5)->toDateString()
    );

    $snippets = $response->json('snippets');
    expect(collect($snippets)->pluck('title'))->toContain('Old Snippet')
        ->and(collect($snippets)->pluck('title'))->not->toContain('New Snippet');
});

// ── Tag autocomplete ──────────────────────────────────────────────────────

test('tag autocomplete returns matching tags from user snippets', function () {
    $user = User::factory()->create();

    Snippet::factory()->create([
        'user_tags' => ['laravel', 'php', 'api'],
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('tags.autocomplete').'?q=lar');

    $response->assertOk();
    expect($response->json('tags'))->toContain('laravel')
        ->and($response->json('tags'))->not->toContain('php');
});

test('tag autocomplete does not return tags from other users snippets', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Snippet::factory()->create([
        'user_tags' => ['secret-tag'],
        'owner_type' => 'App\Models\User', 'owner_id' => $other->id, 'created_by' => $other->id,
    ]);

    $response = $this->actingAs($user)->getJson(route('tags.autocomplete').'?q=secret');

    expect($response->json('tags'))->toBeEmpty();
});

test('tag autocomplete returns empty with no query', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('tags.autocomplete'));

    $response->assertOk();
    expect($response->json('tags'))->toBeEmpty();
});

test('tag autocomplete requires authentication', function () {
    $response = $this->getJson(route('tags.autocomplete').'?q=test');
    $response->assertUnauthorized();
});
