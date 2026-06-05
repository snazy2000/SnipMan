<?php

use App\Models\Folder;
use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Bulk actions ──────────────────────────────────────────────────────────

test('user can bulk delete their own snippets', function () {
    $user = User::factory()->create();
    $snippets = Snippet::factory()->count(3)->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->postJson(route('snippets.bulk'), [
        'action' => 'delete',
        'ids' => $snippets->pluck('id')->toArray(),
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['affected' => 3]);
    expect(Snippet::count())->toBe(0);
});

test('bulk delete ignores snippets owned by others', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Snippet::factory()->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);
    $theirs = Snippet::factory()->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $other->id, 'created_by' => $other->id,
    ]);

    $response = $this->actingAs($user)->postJson(route('snippets.bulk'), [
        'action' => 'delete',
        'ids' => [$mine->id, $theirs->id],
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['affected' => 1]);
    expect(Snippet::find($theirs->id))->not->toBeNull();
    expect(Snippet::find($mine->id))->toBeNull();
});

test('user can bulk move snippets to a folder', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id,
    ]);
    $snippets = Snippet::factory()->count(2)->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->postJson(route('snippets.bulk'), [
        'action' => 'move',
        'ids' => $snippets->pluck('id')->toArray(),
        'folder_id' => $folder->id,
    ]);

    $response->assertOk();
    expect(Snippet::where('folder_id', $folder->id)->count())->toBe(2);
});

test('user can bulk tag snippets', function () {
    $user = User::factory()->create();
    $snippets = Snippet::factory()->count(2)->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
        'user_tags' => [],
    ]);

    $response = $this->actingAs($user)->postJson(route('snippets.bulk'), [
        'action' => 'tag',
        'ids' => $snippets->pluck('id')->toArray(),
        'tags' => ['laravel', 'api'],
    ]);

    $response->assertOk();
    $snippets->each(function ($s) {
        $tags = $s->fresh()->user_tags;
        expect($tags)->toContain('laravel')->toContain('api');
    });
});

test('bulk action requires valid action value', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->postJson(route('snippets.bulk'), [
        'action' => 'destroy_everything',
        'ids' => [$snippet->id],
    ]);

    $response->assertUnprocessable();
});

// ── Export ────────────────────────────────────────────────────────────────

test('user can export their snippets as json', function () {
    $user = User::factory()->create();
    Snippet::factory()->count(2)->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('snippets.export').'?format=json');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json');
    $data = json_decode($response->getContent(), true);
    expect($data['snippets'])->toHaveCount(2);
    expect($data)->toHaveKey('exported_at');
});

test('json export includes expected fields', function () {
    $user = User::factory()->create();
    Snippet::factory()->create([
        'title' => 'Export Me',
        'language' => 'php',
        'description' => 'A test snippet',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'user_tags' => ['tag1'],
    ]);

    $response = $this->actingAs($user)->get(route('snippets.export').'?format=json');

    $data = json_decode($response->getContent(), true);
    $snippet = $data['snippets'][0];

    expect($snippet)->toHaveKeys(['id', 'title', 'description', 'language', 'content', 'tags', 'is_pinned', 'created_at']);
    expect($snippet['title'])->toBe('Export Me');
    expect($snippet['tags'])->toContain('tag1');
});

test('user can export their snippets as zip', function () {
    $user = User::factory()->create();
    Snippet::factory()->create([
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('snippets.export').'?format=zip');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/zip');
});

test('export does not include other users snippets', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Snippet::factory()->create([
        'title' => 'Mine',
        'owner_type' => 'App\Models\User', 'owner_id' => $user->id, 'created_by' => $user->id,
    ]);
    Snippet::factory()->create([
        'title' => 'Not Mine',
        'owner_type' => 'App\Models\User', 'owner_id' => $other->id, 'created_by' => $other->id,
    ]);

    $response = $this->actingAs($user)->get(route('snippets.export').'?format=json');

    $data = json_decode($response->getContent(), true);
    expect($data['snippets'])->toHaveCount(1);
    expect($data['snippets'][0]['title'])->toBe('Mine');
});

test('export requires authentication', function () {
    $response = $this->get(route('snippets.export').'?format=json');
    $response->assertRedirect('/login');
});
