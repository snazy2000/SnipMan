<?php

use App\Models\Folder;
use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('snippet creation validates title is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('snippets.store'), [
        'title' => '',
        'language' => 'javascript',
        'content' => 'console.log("test");',
    ]);

    $response->assertSessionHasErrors('title');
});

test('snippet creation validates language is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('snippets.store'), [
        'title' => 'Test Snippet',
        'language' => '',
        'content' => 'test',
    ]);

    $response->assertSessionHasErrors('language');
});

test('snippet creation validates content is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('snippets.store'), [
        'title' => 'Test Snippet',
        'language' => 'javascript',
        'content' => '',
    ]);

    $response->assertSessionHasErrors('content');
});

test('snippet update validates title is required', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->patch(route('snippets.update', $snippet), [
        'title' => '',
        'language' => $snippet->language,
        'content' => $snippet->content,
    ]);

    $response->assertSessionHasErrors('title');
});

test('snippet clone validates required fields', function () {
    $user = User::factory()->create();
    $original = Snippet::factory()->for($user, 'owner')->create([
        'title' => 'Original',
        'language' => 'javascript',
        'content' => 'console.log("original");',
    ]);

    // Try to clone without required title field
    $response = $this->actingAs($user)->post(route('snippets.clone', $original), [
        'owner_type' => 'personal',
    ]);

    $response->assertSessionHasErrors('title');
});

test('moving snippet to non-existent folder fails validation', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->postJson(route('snippets.move', $snippet), [
        'folder_id' => 99999,
    ]);

    $response->assertStatus(422); // Validation error
});

test('team editor can update team snippet', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'editor']);
    $snippet = Snippet::factory()->for($team, 'owner')->create();

    $response = $this->actingAs($user)->patch(route('snippets.update', $snippet), [
        'title' => 'Updated by Editor',
        'language' => $snippet->language,
        'content' => $snippet->content,
    ]);

    $response->assertRedirect();

    expect($snippet->fresh()->title)->toBe('Updated by Editor');
});
