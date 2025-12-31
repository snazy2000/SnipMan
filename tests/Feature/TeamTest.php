<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('user can create team', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('teams.store'), [
            'name' => 'Test Team',
        ]);

    $response->assertRedirect(route('teams.index'));
    $this->assertDatabaseHas('teams', [
        'name' => 'Test Team',
        'owner_id' => $user->id,
    ]);

    $team = Team::where('name', 'Test Team')->first();
    $this->assertTrue($team->members->contains($user->id));
    $this->assertEquals('owner', $team->members->first()->pivot->role);
});

test('user can update team name', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $team->members()->attach($user->id, ['role' => 'owner']);

    $response = $this
        ->actingAs($user)
        ->patch(route('teams.update', $team), [
            'name' => 'Updated Team Name',
        ]);

    $response->assertRedirect(route('teams.index'));
    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Updated Team Name',
    ]);
});

test('user can delete team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $team->members()->attach($user->id, ['role' => 'owner']);

    $response = $this
        ->actingAs($user)
        ->delete(route('teams.destroy', $team));

    $response->assertRedirect(route('teams.index'));
    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('non-owner cannot update team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($member->id, ['role' => 'editor']);

    $response = $this
        ->actingAs($member)
        ->patch(route('teams.update', $team), [
            'name' => 'Attempted Update',
        ]);

    $response->assertForbidden();
});

test('team owner can invite member by email', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.addMember', $team), [
            'email' => 'newmember@example.com',
            'role' => 'editor',
        ]);

    $response->assertSessionHas('success');

    // Check user was created
    $this->assertDatabaseHas('users', [
        'email' => 'newmember@example.com',
    ]);

    // Check team membership with pending status
    $newUser = User::where('email', 'newmember@example.com')->first();
    $this->assertTrue($team->members->contains($newUser->id));

    $membership = $team->members()->where('user_id', $newUser->id)->first();
    $this->assertEquals('pending', $membership->pivot->invitation_status);
    $this->assertEquals('editor', $membership->pivot->role);

    // Check notification sent
    Notification::assertSentTo($newUser, \App\Notifications\TeamInvitation::class);
});

test('team owner can invite existing user', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.addMember', $team), [
            'email' => 'existing@example.com',
            'role' => 'viewer',
        ]);

    $response->assertSessionHas('success');

    $membership = $team->members()->where('user_id', $existingUser->id)->first();
    $this->assertEquals('pending', $membership->pivot->invitation_status);
    $this->assertEquals('viewer', $membership->pivot->role);
});

test('cannot invite user who is already a member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);
    $team->members()->attach($member->id, ['role' => 'editor']);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.addMember', $team), [
            'email' => 'member@example.com',
            'role' => 'viewer',
        ]);

    $response->assertSessionHasErrors('email');
});

test('team owner can change member role', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);
    $team->members()->attach($member->id, ['role' => 'viewer', 'invitation_status' => 'accepted']);

    $response = $this
        ->actingAs($owner)
        ->patch(route('teams.updateMemberRole', [$team, $member]), [
            'role' => 'editor',
        ]);

    $response->assertSessionHas('success');

    $membership = $team->members()->where('user_id', $member->id)->first();
    $this->assertEquals('editor', $membership->pivot->role);
});

test('cannot change role of team owner', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);

    $response = $this
        ->actingAs($owner)
        ->patch(route('teams.updateMemberRole', [$team, $owner]), [
            'role' => 'editor',
        ]);

    $response->assertSessionHasErrors();
});

test('team owner can remove member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);
    $team->members()->attach($member->id, ['role' => 'editor', 'invitation_status' => 'accepted']);

    $response = $this
        ->actingAs($owner)
        ->delete(route('teams.removeMember', [$team, $member]));

    $response->assertSessionHas('success');
    $this->assertFalse($team->members->contains($member->id));
});

test('cannot remove team owner', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);

    $response = $this
        ->actingAs($owner)
        ->delete(route('teams.removeMember', [$team, $owner]));

    $response->assertSessionHasErrors();
});

test('team owner can resend invitation', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);

    $pendingUser = User::factory()->create(['email' => 'pending@example.com']);
    $team->members()->attach($pendingUser->id, [
        'role' => 'editor',
        'invitation_status' => 'pending',
        'invitation_token' => hash('sha256', 'test-token'),
        'invited_at' => now()->subDays(2),
    ]);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.resendInvitation', [$team, $pendingUser]));

    $response->assertSessionHas('success');
    Notification::assertSentTo($pendingUser, \App\Notifications\TeamInvitation::class);
});

test('non-owner cannot manage team members', function () {
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($editor->id, ['role' => 'editor']);

    $response = $this
        ->actingAs($editor)
        ->post(route('teams.addMember', $team), [
            'email' => 'test@example.com',
            'role' => 'viewer',
        ]);

    $response->assertForbidden();
});

test('user can view teams they are a member of', function () {
    $user = User::factory()->create();
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();

    $team1->members()->attach($user->id, ['role' => 'editor']);
    $team2->members()->attach($user->id, ['role' => 'viewer']);

    $response = $this
        ->actingAs($user)
        ->get(route('teams.index'));

    $response->assertOk();
    $response->assertSee($team1->name);
    $response->assertSee($team2->name);
});
