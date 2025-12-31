<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('create super admin command with all options', function () {
    $this->artisan('make:superadmin', [
        '--name' => 'Admin User',
        '--email' => 'admin@example.com',
        '--password' => 'SecurePassword123',
    ])->assertSuccessful();

    $user = User::where('email', 'admin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Admin User')
        ->and($user->is_super_admin)->toBeTrue()
        ->and(Hash::check('SecurePassword123', $user->password))->toBeTrue();
});

test('create super admin command validates password length', function () {
    $this->artisan('make:superadmin', [
        '--name' => 'Admin User',
        '--email' => 'short@example.com',
        '--password' => 'short',
    ])->assertFailed();

    expect(User::where('email', 'short@example.com')->exists())->toBeFalse();
});

test('create super admin command validates unique email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->artisan('make:superadmin', [
        '--name' => 'Admin User',
        '--email' => 'existing@example.com',
        '--password' => 'SecurePassword123',
    ])->assertFailed();
});

test('super admin user is created with verified email', function () {
    $this->artisan('make:superadmin', [
        '--name' => 'Test Admin',
        '--email' => 'test@example.com',
        '--password' => 'Password123',
    ])->assertSuccessful();

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_super_admin)->toBeTrue();
});
