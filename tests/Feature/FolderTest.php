<?php

use App\Models\Folder;
use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create personal folder', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('folders.store'), [
            'name' => 'Test Folder',
            'owner_type' => 'personal',
        ]);

    $response->assertRedirect(route('folders.index'));
    $this->assertDatabaseHas('folders', [
        'name' => 'Test Folder',
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);
});

test('user can create team folder', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $team->members()->attach($user->id, ['role' => 'owner']);

    $response = $this
        ->actingAs($user)
        ->post(route('folders.store'), [
            'name' => 'Team Folder',
            'owner_type' => 'team',
            'team_id' => $team->id,
        ]);

    $response->assertRedirect(route('folders.index'));
    $this->assertDatabaseHas('folders', [
        'name' => 'Team Folder',
        'owner_type' => 'App\Models\Team',
        'owner_id' => $team->id,
    ]);
});

test('user can create subfolder', function () {
    $user = User::factory()->create();
    $parentFolder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('folders.store'), [
            'name' => 'Subfolder',
            'owner_type' => 'personal',
            'parent_id' => $parentFolder->id,
        ]);

    $response->assertRedirect(route('folders.index'));
    $this->assertDatabaseHas('folders', [
        'name' => 'Subfolder',
        'parent_id' => $parentFolder->id,
    ]);
});

test('user can update folder name', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
        'name' => 'Old Name',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('folders.update', $folder), [
            'name' => 'New Name',
        ]);

    $response->assertRedirect(route('folders.index'));
    $this->assertDatabaseHas('folders', [
        'id' => $folder->id,
        'name' => 'New Name',
    ]);
});

test('user can delete empty folder', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('folders.destroy', $folder));

    $response->assertRedirect(route('folders.index'));
    $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
});

test('user cannot create folder in team without permissions', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user->id, ['role' => 'viewer']);

    $response = $this
        ->actingAs($user)
        ->post(route('folders.store'), [
            'name' => 'Test Folder',
            'owner_type' => 'team',
            'team_id' => $team->id,
        ]);

    $response->assertForbidden();
});

test('user can view folder contents', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('folders.show', $folder));

    $response->assertOk();
    $response->assertSee($folder->name);
});

test('user cannot view other users private folders', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $otherUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('folders.show', $folder));

    $response->assertForbidden();
});

test('user can move folder to different parent', function () {
    $user = User::factory()->create();
    $parentFolder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);
    $childFolder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('folders.move', $childFolder), [
            'parent_id' => $parentFolder->id,
        ]);

    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('folders', [
        'id' => $childFolder->id,
        'parent_id' => $parentFolder->id,
    ]);
});
