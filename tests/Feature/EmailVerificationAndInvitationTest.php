<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $this->assertTrue($user->fresh()->hasVerifiedEmail());
    $response->assertRedirect(route('dashboard').'?verified=1');
});

test('email verification notification can be resent', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this
        ->actingAs($user)
        ->post('/email/verification-notification');

    Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
});

test('user can accept invitation and set password', function () {
    $token = \Illuminate\Support\Str::random(64);
    $user = User::factory()->create([
        'name' => 'Test User',
        'invitation_token' => hash('sha256', $token),
        'email_verified_at' => null,
    ]);

    $response = $this->get(route('invitation.show', ['token' => $token]));

    $response->assertOk();
    $response->assertViewIs('auth.accept-invitation');

    // Set password
    $response = $this->post(route('invitation.accept', ['token' => $token]), [
        'name' => 'Test User',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertRedirect(route('snippets.index'));

    $user->refresh();
    $this->assertNull($user->invitation_token);
    $this->assertNotNull($user->email_verified_at);
    $this->assertNotNull($user->invitation_accepted_at);
});

test('invalid invitation token shows error', function () {
    $response = $this->get(route('invitation.show', ['token' => 'invalid-token']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('used invitation token cannot be reused', function () {
    $user = User::factory()->create([
        'invitation_token' => null,
        'invitation_accepted_at' => now(),
    ]);

    $response = $this->get(route('invitation.show', ['token' => 'any-token']));

    $response->assertRedirect(route('login'));
});
