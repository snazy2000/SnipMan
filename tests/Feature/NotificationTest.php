<?php

use App\Models\Team;
use App\Models\User;
use App\Notifications\TeamInvitation;
use App\Notifications\UserInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

// UserInvitation Tests
test('user invitation notification contains correct data', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'invitation_token' => hash('sha256', 'test-token-123'),
    ]);

    $notification = new UserInvitation('test-token-123');
    $mailData = $notification->toMail($user);

    expect($mailData->subject)->toBe('Welcome to '.config('app.name').' - Complete Your Account Setup')
        ->and($mailData->markdown)->toBe('emails.invitation');
});

test('user invitation uses mail channel', function () {
    $user = User::factory()->create();
    $notification = new UserInvitation('test-token');

    expect($notification->via($user))->toBe(['mail']);
});

test('user invitation can be sent', function () {
    Notification::fake();

    $user = User::factory()->create([
        'invitation_token' => hash('sha256', 'token-123'),
    ]);

    $user->notify(new UserInvitation('token-123'));

    Notification::assertSentTo($user, UserInvitation::class);
});

// TeamInvitation Tests
test('team invitation notification contains correct data', function () {
    $team = Team::factory()->create(['name' => 'Development Team']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);

    // Test for existing user (isNewUser = false)
    $notification = new TeamInvitation($team, 'editor', 'test-token-456', false);
    $mailData = $notification->toMail($invitedUser);

    expect($mailData->subject)->toBe('Team Invitation: Join Development Team')
        ->and($mailData->markdown)->toBe('emails.team-invitation');
});

test('team invitation uses mail channel', function () {
    $team = Team::factory()->create();
    $invitedUser = User::factory()->create();

    $notification = new TeamInvitation($team, 'viewer', 'token', false);

    expect($notification->via($invitedUser))->toBe(['mail']);
});

test('team invitation can be sent', function () {
    Notification::fake();

    $team = Team::factory()->create();
    $invitedUser = User::factory()->create();

    $invitedUser->notify(new TeamInvitation($team, 'owner', 'token-789', false));

    Notification::assertSentTo($invitedUser, TeamInvitation::class);
});

test('team invitation for new user has correct subject', function () {
    $team = Team::factory()->create(['name' => 'Test Team']);
    $invitedUser = User::factory()->create();

    // Test new user invitation (isNewUser = true)
    $notification = new TeamInvitation($team, 'editor', 'token', true);
    $mailData = $notification->toMail($invitedUser);

    expect($mailData->subject)->toBe('You\'ve been invited to join Test Team on '.config('app.name'));
});
