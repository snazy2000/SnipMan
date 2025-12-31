<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_super_admin' => true]);
    $this->user = User::factory()->create(['is_super_admin' => false]);
});

test('admin can view admin dashboard', function () {
    $response = $this
        ->actingAs($this->admin)
        ->get(route('admin.index'));

    $response->assertOk();
    $response->assertViewIs('admin.index');
});

test('non-admin cannot access admin dashboard', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.index'));

    $response->assertForbidden();
});

test('admin can view users list', function () {
    $response = $this
        ->actingAs($this->admin)
        ->get(route('admin.users'));

    $response->assertOk();
    $response->assertViewIs('admin.users.index');
    $response->assertViewHas('users');
});

test('admin can search users', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $response = $this
        ->actingAs($this->admin)
        ->get(route('admin.users', ['search' => 'john']));

    $response->assertOk();
    $response->assertSee('John Doe');
    $response->assertDontSee('Jane Smith');
});

test('admin can filter users by status', function () {
    $activeUser = User::factory()->create(['is_disabled' => false, 'invitation_token' => null]);
    $disabledUser = User::factory()->create(['is_disabled' => true]);
    $pendingUser = User::factory()->create(['invitation_token' => 'token123']);

    // Filter active users
    $response = $this
        ->actingAs($this->admin)
        ->get(route('admin.users', ['status' => 'active']));

    $response->assertOk();
    $response->assertSee($activeUser->email);

    // Filter disabled users
    $response = $this
        ->actingAs($this->admin)
        ->get(route('admin.users', ['status' => 'disabled']));

    $response->assertOk();
    $response->assertSee($disabledUser->email);
});

test('admin can create user with invitation', function () {
    Notification::fake();

    $response = $this
        ->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'is_super_admin' => false,
        ]);

    $response->assertRedirect(route('admin.users'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
    ]);

    $user = User::where('email', 'newuser@example.com')->first();
    $this->assertNotNull($user->invitation_token);

    Notification::assertSentTo($user, \App\Notifications\UserInvitation::class);
});

test('admin can update user', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->patch(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'is_super_admin' => true,
        ]);

    $response->assertRedirect(route('admin.users'));

    $user->refresh();
    $this->assertEquals('Updated Name', $user->name);
    $this->assertTrue($user->is_super_admin);
});

test('admin cannot remove own super admin privileges', function () {
    $response = $this
        ->actingAs($this->admin)
        ->patch(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'is_super_admin' => false,
        ]);

    $this->admin->refresh();
    $this->assertTrue($this->admin->is_super_admin);
});

test('admin can resend user invitation', function () {
    Notification::fake();

    $user = User::factory()->create(['invitation_token' => 'oldtoken']);

    $response = $this
        ->actingAs($this->admin)
        ->post(route('admin.users.resendInvitation', $user));

    $response->assertRedirect(route('admin.users'));
    $response->assertSessionHas('success');

    Notification::assertSentTo($user, \App\Notifications\UserInvitation::class);
});

test('admin can delete user', function () {
    $userToDelete = User::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $userToDelete));

    $response->assertRedirect(route('admin.users'));

    $this->assertTrue($userToDelete->fresh()->trashed());
    $this->assertTrue($userToDelete->fresh()->is_disabled);
});

test('admin cannot delete themselves', function () {
    $response = $this
        ->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin));

    $response->assertRedirect(route('admin.users'));
    $response->assertSessionHas('error');

    $this->assertFalse($this->admin->fresh()->trashed());
});

test('admin can disable user', function () {
    $userToDisable = User::factory()->create(['is_disabled' => false]);

    $response = $this
        ->actingAs($this->admin)
        ->post(route('admin.users.disable', $userToDisable));

    $response->assertRedirect(route('admin.users'));

    $userToDisable->refresh();
    $this->assertTrue($userToDisable->is_disabled);
});

test('admin can enable disabled user', function () {
    $disabledUser = User::factory()->create(['is_disabled' => true]);

    $response = $this
        ->actingAs($this->admin)
        ->post(route('admin.users.enable', $disabledUser));

    $response->assertRedirect(route('admin.users'));

    $disabledUser->refresh();
    $this->assertFalse($disabledUser->is_disabled);
});

test('admin can view teams list', function () {
    Team::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->admin)
        ->get(route('admin.teams'));

    $response->assertOk();
    $response->assertViewIs('admin.teams.index');
    $response->assertViewHas('teams');
});

test('admin can update team', function () {
    $team = Team::factory()->create();
    $newOwner = User::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->patch(route('admin.teams.update', $team), [
            'name' => 'Updated Team Name',
            'owner_id' => $newOwner->id,
        ]);

    $response->assertRedirect(route('admin.teams'));

    $team->refresh();
    $this->assertEquals('Updated Team Name', $team->name);
    $this->assertEquals($newOwner->id, $team->owner_id);
});

test('admin can delete team', function () {
    $team = Team::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->delete(route('admin.teams.destroy', $team));

    $response->assertRedirect(route('admin.teams'));

    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('admin can delete user and transfer team ownership to another member', function () {
    $teamOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $team = Team::factory()->for($teamOwner, 'owner')->create(['name' => 'Test Team']);

    // Add both users as team members
    $team->members()->attach($teamOwner->id, ['role' => 'owner']);
    $team->members()->attach($newOwner->id, ['role' => 'editor']);

    // Create some team content with multiple versions
    $snippet = \App\Models\Snippet::factory()->for($team, 'owner')->create([
        'content' => 'Version 1 content',
        'created_by' => $teamOwner->id,
    ]);

    // Create initial version
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Version 1 content',
        'created_by' => $teamOwner->id,
    ]);

    // Update snippet to create version 2
    $snippet->update(['content' => 'Version 2 content']);
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 2,
        'content' => 'Version 2 content',
        'created_by' => $teamOwner->id,
    ]);

    // Update snippet to create version 3
    $snippet->update(['content' => 'Version 3 content']);
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 3,
        'content' => 'Version 3 content',
        'created_by' => $teamOwner->id,
    ]);

    $folder = \App\Models\Folder::factory()->for($team, 'owner')->create();

    // Verify we have 3 versions
    expect($snippet->versions()->count())->toBe(3);

    // Delete user with team ownership transfer
    $response = $this
        ->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $teamOwner), [
            'team_owners' => [
                $team->id => $newOwner->id,
            ],
        ]);

    $response->assertRedirect(route('admin.users'));

    // Verify team ownership was transferred
    $team->refresh();
    expect($team->owner_id)->toBe($newOwner->id);

    // Verify new owner has owner role in team members
    $membership = $team->members()->where('user_id', $newOwner->id)->first();
    expect($membership->pivot->role)->toBe('owner');

    // Verify team content still exists
    expect(\App\Models\Snippet::find($snippet->id))->not->toBeNull();
    expect(\App\Models\Folder::find($folder->id))->not->toBeNull();

    // Verify all snippet versions were preserved
    $snippet->refresh();
    expect($snippet->versions()->count())->toBe(3);
    expect($snippet->versions()->pluck('version_number')->toArray())->toBe([3, 2, 1]);

    // Verify user was soft deleted
    expect($teamOwner->fresh()->trashed())->toBeTrue();
});

test('admin can delete user and delete team when no ownership transfer specified', function () {
    $teamOwner = User::factory()->create();
    $team = Team::factory()->for($teamOwner, 'owner')->create(['name' => 'Test Team']);

    // Add user as team member
    $team->members()->attach($teamOwner->id, ['role' => 'owner']);

    // Create team content with multiple versions
    $snippet = \App\Models\Snippet::factory()->for($team, 'owner')->create([
        'content' => 'Version 1 content',
        'created_by' => $teamOwner->id,
    ]);

    // Create initial version
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 1,
        'content' => 'Version 1 content',
        'created_by' => $teamOwner->id,
    ]);

    // Update snippet to create version 2
    $snippet->update(['content' => 'Version 2 content']);
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 2,
        'content' => 'Version 2 content',
        'created_by' => $teamOwner->id,
    ]);

    // Update snippet to create version 3
    $snippet->update(['content' => 'Version 3 content']);
    \App\Models\SnippetVersion::create([
        'snippet_id' => $snippet->id,
        'version_number' => 3,
        'content' => 'Version 3 content',
        'created_by' => $teamOwner->id,
    ]);

    $folder = \App\Models\Folder::factory()->for($team, 'owner')->create();

    // Verify we have 3 versions before deletion
    expect($snippet->versions()->count())->toBe(3);

    // Store IDs for later verification
    $teamId = $team->id;
    $snippetId = $snippet->id;
    $folderId = $folder->id;
    $versionIds = $snippet->versions()->pluck('id')->toArray();

    // Delete user without specifying team ownership transfer
    $response = $this
        ->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $teamOwner), [
            'team_owners' => [],
        ]);

    $response->assertRedirect(route('admin.users'));

    // Verify team was deleted
    expect(Team::find($teamId))->toBeNull();

    // Verify team content was deleted
    expect(\App\Models\Snippet::find($snippetId))->toBeNull();
    expect(\App\Models\Folder::find($folderId))->toBeNull();

    // Verify all snippet versions were deleted
    foreach ($versionIds as $versionId) {
        expect(\App\Models\SnippetVersion::find($versionId))->toBeNull();
    }
    expect(\App\Models\SnippetVersion::whereIn('id', $versionIds)->count())->toBe(0);

    // Verify user was soft deleted
    expect($teamOwner->fresh()->trashed())->toBeTrue();
});
