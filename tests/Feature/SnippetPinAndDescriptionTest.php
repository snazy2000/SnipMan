<?php

use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Description field ─────────────────────────────────────────────────────

test('user can create snippet with description', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('snippets.store'), [
        'title' => 'My Snippet',
        'description' => 'A helpful description',
        'language' => 'php',
        'content' => '<?php echo "hello";',
        'owner_type' => 'personal',
    ]);

    $response->assertRedirect();
    expect(Snippet::first()->description)->toBe('A helpful description');
});

test('description is optional when creating a snippet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('snippets.store'), [
        'title' => 'No Description',
        'language' => 'php',
        'content' => '<?php',
        'owner_type' => 'personal',
    ]);

    expect(Snippet::first()->description)->toBeNull();
});

test('description is limited to 1000 characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('snippets.store'), [
        'title' => 'Test',
        'description' => str_repeat('a', 1001),
        'language' => 'php',
        'content' => '<?php',
        'owner_type' => 'personal',
    ]);

    $response->assertSessionHasErrors('description');
});

test('user can update snippet description', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'description' => 'Old description',
    ]);

    $this->actingAs($user)->patch(route('snippets.update', $snippet), [
        'title' => $snippet->title,
        'language' => $snippet->language,
        'content' => $snippet->content,
        'description' => 'New description',
    ]);

    expect($snippet->fresh()->description)->toBe('New description');
});

// ── Pin / unpin ───────────────────────────────────────────────────────────

test('snippet is unpinned by default', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
    ]);

    expect($snippet->fresh()->is_pinned)->toBeFalse();
});

test('owner can pin a snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'is_pinned' => false,
    ]);

    $response = $this->actingAs($user)->post(route('snippets.pin', $snippet));

    $response->assertOk();
    $response->assertJsonFragment(['is_pinned' => true]);
    expect($snippet->fresh()->is_pinned)->toBeTrue();
});

test('owner can unpin a pinned snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'is_pinned' => true,
    ]);

    $response = $this->actingAs($user)->post(route('snippets.pin', $snippet));

    $response->assertOk();
    $response->assertJsonFragment(['is_pinned' => false]);
    expect($snippet->fresh()->is_pinned)->toBeFalse();
});

test('unauthorized user cannot pin another users snippet', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $owner->id,
        'created_by' => $owner->id,
    ]);

    $response = $this->actingAs($other)->post(route('snippets.pin', $snippet));

    $response->assertForbidden();
    expect($snippet->fresh()->is_pinned)->toBeFalse();
});

test('pinned snippets appear first on index', function () {
    $user = User::factory()->create();

    $unpinned = Snippet::factory()->create([
        'title' => 'Unpinned',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'is_pinned' => false,
    ]);

    $pinned = Snippet::factory()->create([
        'title' => 'Pinned',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'is_pinned' => true,
    ]);

    $response = $this->actingAs($user)->get(route('snippets.index'));

    $response->assertOk();
    $content = $response->getContent();
    expect(strpos($content, 'Pinned'))->toBeLessThan(strpos($content, 'Unpinned'));
});
