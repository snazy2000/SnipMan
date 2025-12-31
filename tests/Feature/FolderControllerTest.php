<?php

use App\Models\Folder;
use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view folder with breadcrumbs', function () {
    $user = User::factory()->create();
    $parentFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Parent']);
    $childFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Child', 'parent_id' => $parentFolder->id]);

    $response = $this->actingAs($user)->get(route('folders.show', $childFolder));

    $response->assertOk()
        ->assertSee('Parent')
        ->assertSee('Child');
});

test('user cannot access folder without permission', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $folder = Folder::factory()->for($otherUser, 'owner')->create();

    $response = $this->actingAs($user)->get(route('folders.show', $folder));

    $response->assertForbidden();
});

test('user can create root level folder', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('folders.store'), [
        'name' => 'Root Folder',
        'parent_id' => null,
        'owner_type' => 'personal',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('folders', [
        'name' => 'Root Folder',
        'owner_id' => $user->id,
        'owner_type' => User::class,
        'parent_id' => null,
    ]);
});

test('folder deletion removes all child folders', function () {
    $user = User::factory()->create();
    $parentFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Parent']);
    $childFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Child', 'parent_id' => $parentFolder->id]);
    $grandchildFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Grandchild', 'parent_id' => $childFolder->id]);

    $response = $this->actingAs($user)->delete(route('folders.destroy', $parentFolder));

    $response->assertRedirect();

    expect(Folder::find($parentFolder->id))->toBeNull()
        ->and(Folder::find($childFolder->id))->toBeNull()
        ->and(Folder::find($grandchildFolder->id))->toBeNull();
});

test('folder deletion moves snippets to root', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user, 'owner')->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create(['folder_id' => $folder->id]);

    $response = $this->actingAs($user)->delete(route('folders.destroy', $folder));

    $response->assertRedirect();

    expect(Folder::find($folder->id))->toBeNull();

    $snippet->refresh();
    expect($snippet->folder_id)->toBeNull();
});

test('moving folder validates parent exists', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->patch(route('folders.update', $folder), [
        'parent_id' => 99999, // Non-existent folder
    ]);

    $response->assertSessionHasErrors('parent_id');
});

test('user cannot move folder to create circular reference', function () {
    $user = User::factory()->create();
    $parentFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Parent']);
    $childFolder = Folder::factory()->for($user, 'owner')->create(['name' => 'Child', 'parent_id' => $parentFolder->id]);

    // Try to move parent inside child (circular reference)
    $response = $this->actingAs($user)->postJson(route('folders.move', $parentFolder), [
        'parent_id' => $childFolder->id,
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Cannot move folder to its own descendant.',
        ]);
});

test('team member can view team folder', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user);
    $folder = Folder::factory()->for($team, 'owner')->create();

    $response = $this->actingAs($user)->get(route('folders.show', $folder));

    $response->assertOk();
});

test('folder update validates name is required', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->patch(route('folders.update', $folder), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
});
