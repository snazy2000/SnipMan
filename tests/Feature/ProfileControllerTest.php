<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('profile update validates name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => '',
        'email' => $user->email,
    ]);

    $response->assertSessionHasErrors('name');
});

test('profile update validates email is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => '',
    ]);

    $response->assertSessionHasErrors('email');
});

test('profile update validates email format', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('profile update validates email uniqueness', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('user can update monaco theme preference', function () {
    $user = User::factory()->create(['monaco_theme' => 'vs-dark']);

    $response = $this->actingAs($user)->patch(route('profile.theme.update'), [
        'monaco_theme' => 'vs-light',
    ]);

    $response->assertRedirect();

    expect($user->fresh()->monaco_theme)->toBe('vs-light');
});

test('user can update monaco language preference', function () {
    $user = User::factory()->create(['monaco_language' => 'javascript']);

    $response = $this->actingAs($user)->patch(route('profile.language.update'), [
        'monaco_language' => 'python',
    ]);

    $response->assertRedirect();

    expect($user->fresh()->monaco_language)->toBe('python');
});

test('password update validates current password is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password' => '',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ]);

    $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
});

test('password update validates new password length', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password' => 'password',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrorsIn('updatePassword', 'password');
});

test('password update validates password confirmation matches', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password' => 'password',
        'password' => 'NewPassword123',
        'password_confirmation' => 'DifferentPassword123',
    ]);

    $response->assertSessionHasErrorsIn('updatePassword', 'password');
});

test('account deletion validates password is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => '',
    ]);

    $response->assertSessionHasErrorsIn('userDeletion', 'password');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');

    // User should be soft deleted
    expect($user->fresh()->trashed())->toBeTrue();
});
