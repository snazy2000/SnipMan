<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Models\Folder;
use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── EnsureUserIsActive — soft deleted users ───────────────────────────────

test('soft deleted user is logged out on next request', function () {
    $user = User::factory()->create();
    $user->delete(); // soft delete

    $response = $this->actingAs($user)->get(route('snippets.index'));

    $response->assertRedirect('/login');
});

test('disabled user is logged out on next request', function () {
    $user = User::factory()->create(['is_disabled' => true]);

    $response = $this->actingAs($user)->get(route('snippets.index'));

    $response->assertRedirect('/login');
});

test('active user can access protected routes', function () {
    $user = User::factory()->create(['is_disabled' => false]);

    $response = $this->actingAs($user)->get(route('snippets.index'));

    $response->assertOk();
});

// ── is_super_admin not mass-assignable ───────────────────────────────────

test('is_super_admin cannot be set via mass assignment', function () {
    $user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'is_super_admin' => true, // should be ignored
    ]);

    // is_super_admin is not in $fillable so it should not be set (remains null/false)
    expect($user->fresh()->is_super_admin)->not->toBeTrue();
});

// ── Snippet create: folder_id authorization ───────────────────────────────

test('cannot preselect a folder belonging to another user on snippet create', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $otherFolder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $other->id,
    ]);

    $response = $this->actingAs($user)
        ->get(route('snippets.create').'?folder_id='.$otherFolder->id);

    $response->assertForbidden();
});

test('can preselect own folder on snippet create', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->get(route('snippets.create').'?folder_id='.$folder->id);

    $response->assertOk();
});

// ── Admin: team owner transfer validation ────────────────────────────────

test('admin cannot assign a disabled user as team owner', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    $admin->is_super_admin = true;
    $admin->save();

    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    $disabled = User::factory()->create(['is_disabled' => true]);

    $response = $this->actingAs($admin)->patch(route('admin.teams.update', $team), [
        'name' => $team->name,
        'owner_id' => $disabled->id,
    ]);

    $response->assertSessionHasErrors('owner_id');
    expect($team->fresh()->owner_id)->toBe($owner->id);
});

test('admin cannot assign a soft-deleted user as team owner', function () {
    $admin = User::factory()->create();
    $admin->is_super_admin = true;
    $admin->save();

    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    $deleted = User::factory()->create();
    $deleted->delete();

    $response = $this->actingAs($admin)->patch(route('admin.teams.update', $team), [
        'name' => $team->name,
        'owner_id' => $deleted->id,
    ]);

    $response->assertSessionHasErrors('owner_id');
    expect($team->fresh()->owner_id)->toBe($owner->id);
});

test('admin can transfer team to active user', function () {
    $admin = User::factory()->create();
    $admin->is_super_admin = true;
    $admin->save();

    $owner = User::factory()->create();
    $newOwner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($admin)->patch(route('admin.teams.update', $team), [
        'name' => $team->name,
        'owner_id' => $newOwner->id,
    ]);

    $response->assertRedirect();
    expect($team->fresh()->owner_id)->toBe($newOwner->id);
});

// ── API folder ownership validation ──────────────────────────────────────

test('api cannot create snippet in folder belonging to different owner', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $otherUser = User::factory()->create();
    $otherFolder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $otherUser->id,
    ]);

    $response = $this->withToken($token)->postJson('/api/snippets', [
        'title' => 'Test',
        'language' => 'php',
        'content' => '<?php',
        'owner_type' => 'personal',
        'folder_id' => $otherFolder->id,
    ]);

    $response->assertForbidden();
});

test('api can create snippet in own folder', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $folder = Folder::factory()->create([
        'owner_type' => 'App\Models\User',
        'owner_id' => $user->id,
    ]);

    $response = $this->withToken($token)->postJson('/api/snippets', [
        'title' => 'Test',
        'language' => 'php',
        'content' => '<?php',
        'owner_type' => 'personal',
        'folder_id' => $folder->id,
    ]);

    $response->assertCreated();
});
