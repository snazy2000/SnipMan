<?php

use App\Models\AISetting;
use App\Models\Folder;
use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Regression tests for the reviewed security findings
|--------------------------------------------------------------------------
|
| One test per fixed finding, named with its id so a future failure points
| straight back at what it is protecting.
|
*/

// ── H1: API team filter must be scoped to membership ──────────────────────

test('H1: api owner=team filter does not leak snippets from a team the caller is not in', function () {
    $caller = User::factory()->create();
    $token = $caller->createToken('test')->plainTextToken;

    $outsiderTeam = Team::factory()->create();
    Snippet::factory()->create([
        'title' => 'Confidential deploy keys',
        'owner_type' => Team::class,
        'owner_id' => $outsiderTeam->id,
        'folder_id' => null,
    ]);

    $response = $this->withToken($token)->getJson('/api/snippets?owner=team:'.$outsiderTeam->id);

    $response->assertOk()
        ->assertJsonPath('total', 0)
        ->assertJsonPath('data', []);
    expect($response->json())->not->toContain('Confidential deploy keys');
});

test('H1: api owner=team filter still returns snippets from the callers own team', function () {
    $caller = User::factory()->create();
    $token = $caller->createToken('test')->plainTextToken;

    $team = Team::factory()->create();
    $team->members()->attach($caller->id, ['role' => 'editor']);

    Snippet::factory()->create([
        'title' => 'Our deploy notes',
        'owner_type' => Team::class,
        'owner_id' => $team->id,
        'folder_id' => null,
    ]);

    $response = $this->withToken($token)->getJson('/api/snippets?owner=team:'.$team->id);

    $response->assertOk()->assertJsonPath('total', 1);
    expect($response->json('data.0.title'))->toBe('Our deploy notes');
});

// ── H2: bulk move must authorize the destination folder ───────────────────

test('H2: bulk move into another users folder is refused and changes nothing', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => User::class,
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'folder_id' => null,
    ]);

    $stranger = User::factory()->create();
    $strangersFolder = Folder::factory()->create([
        'owner_type' => User::class,
        'owner_id' => $stranger->id,
    ]);

    $response = $this->actingAs($user)->postJson(route('snippets.bulk'), [
        'action' => 'move',
        'ids' => [$snippet->id],
        'folder_id' => $strangersFolder->id,
    ]);

    $response->assertForbidden();
    expect($snippet->fresh()->folder_id)->toBeNull();
});

test('H2: bulk move into the callers own folder still works', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => User::class,
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'folder_id' => null,
    ]);
    $folder = Folder::factory()->create([
        'owner_type' => User::class,
        'owner_id' => $user->id,
    ]);

    $this->actingAs($user)->postJson(route('snippets.bulk'), [
        'action' => 'move',
        'ids' => [$snippet->id],
        'folder_id' => $folder->id,
    ])->assertOk();

    expect($snippet->fresh()->folder_id)->toBe($folder->id);
});

// ── M2: public shares must not outlive the account ────────────────────────

test('M2: deleting your own account takes its public share links offline', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->create([
        'owner_type' => User::class,
        'owner_id' => $user->id,
        'created_by' => $user->id,
        'folder_id' => null,
    ]);
    $share = $snippet->shares()->create(['is_active' => true, 'views' => 0]);

    // Live before deletion.
    $this->get('/s/'.$share->uuid)->assertOk();

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password']);

    expect($share->fresh()->is_active)->toBeFalse();
    $this->get('/s/'.$share->uuid)->assertNotFound();
});

test('M2: an admin deleting a user takes that users share links offline', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    $victim = User::factory()->create();

    $snippet = Snippet::factory()->create([
        'owner_type' => User::class,
        'owner_id' => $victim->id,
        'created_by' => $victim->id,
        'folder_id' => null,
    ]);
    $share = $snippet->shares()->create(['is_active' => true, 'views' => 0]);

    $this->actingAs($admin)->delete(route('admin.users.destroy', $victim));

    expect($share->fresh()->is_active)->toBeFalse();
    $this->get('/s/'.$share->uuid)->assertNotFound();
});

test('M2: revoking shares leaves other users shares untouched', function () {
    $user = User::factory()->create();
    $bystander = User::factory()->create();

    $mine = Snippet::factory()->create([
        'owner_type' => User::class, 'owner_id' => $user->id,
        'created_by' => $user->id, 'folder_id' => null,
    ]);
    $theirs = Snippet::factory()->create([
        'owner_type' => User::class, 'owner_id' => $bystander->id,
        'created_by' => $bystander->id, 'folder_id' => null,
    ]);

    $mineShare = $mine->shares()->create(['is_active' => true, 'views' => 0]);
    $theirShare = $theirs->shares()->create(['is_active' => true, 'views' => 0]);

    $user->revokePublicShares();

    expect($mineShare->fresh()->is_active)->toBeFalse()
        ->and($theirShare->fresh()->is_active)->toBeTrue();
});

// ── M3: is_super_admin must read the value, not the key's presence ─────────

test('M3: submitting is_super_admin=0 does not grant super admin', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    $target = User::factory()->create(['is_super_admin' => false]);

    $this->actingAs($admin)->patch(route('admin.users.update', $target), [
        'name' => 'Target User',
        'email' => $target->email,
        'is_super_admin' => 0,
    ]);

    expect($target->fresh()->is_super_admin)->toBeFalse();
});

test('M3: submitting is_super_admin=1 does grant super admin', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    $target = User::factory()->create(['is_super_admin' => false]);

    $this->actingAs($admin)->patch(route('admin.users.update', $target), [
        'name' => 'Target User',
        'email' => $target->email,
        'is_super_admin' => 1,
    ]);

    expect($target->fresh()->is_super_admin)->toBeTrue();
});

test('M3: omitting is_super_admin revokes it', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    $target = User::factory()->create(['is_super_admin' => true]);

    $this->actingAs($admin)->patch(route('admin.users.update', $target), [
        'name' => 'Target User',
        'email' => $target->email,
    ]);

    expect($target->fresh()->is_super_admin)->toBeFalse();
});

// ── M4: invitation tokens must expire ─────────────────────────────────────

test('M4: a fresh invitation link is accepted', function () {
    $token = Str::random(64);
    $invitee = User::factory()->create([
        'invitation_token' => hash('sha256', $token),
        'invitation_accepted_at' => null,
        'invitation_expires_at' => now()->addDays(3),
    ]);

    $this->get(route('invitation.show', ['token' => $token]))->assertOk();

    $this->post(route('invitation.accept', ['token' => $token]), [
        'name' => 'New Person',
        'password' => 'Str0ng-Passw0rd!',
        'password_confirmation' => 'Str0ng-Passw0rd!',
    ])->assertRedirect(route('snippets.index'));

    expect($invitee->fresh()->invitation_accepted_at)->not->toBeNull();
});

test('M4: an expired invitation link is rejected and cannot set a password', function () {
    $token = Str::random(64);
    $invitee = User::factory()->create([
        'invitation_token' => hash('sha256', $token),
        'invitation_accepted_at' => null,
        'invitation_expires_at' => now()->subDay(),
    ]);

    $this->get(route('invitation.show', ['token' => $token]))
        ->assertRedirect(route('login'));

    $this->post(route('invitation.accept', ['token' => $token]), [
        'name' => 'Intruder',
        'password' => 'Str0ng-Passw0rd!',
        'password_confirmation' => 'Str0ng-Passw0rd!',
    ])->assertRedirect(route('login'));

    expect($invitee->fresh()->invitation_accepted_at)->toBeNull()
        ->and($invitee->fresh()->invitation_token)->not->toBeNull();
});

test('M4: an invitation with no expiry recorded still works', function () {
    // Invitations issued before the expiry column existed must not break.
    $token = Str::random(64);
    User::factory()->create([
        'invitation_token' => hash('sha256', $token),
        'invitation_accepted_at' => null,
        'invitation_expires_at' => null,
    ]);

    $this->get(route('invitation.show', ['token' => $token]))->assertOk();
});

test('M4: admin-created invitations carry an expiry', function () {
    Notification::fake();
    $admin = User::factory()->create(['is_super_admin' => true]);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Invited Person',
        'email' => 'invited@example.com',
    ]);

    $created = User::where('email', 'invited@example.com')->firstOrFail();

    expect($created->invitation_expires_at)->not->toBeNull()
        ->and($created->invitation_expires_at->isFuture())->toBeTrue();
});

// ── M4/M5: team invitation acceptance ─────────────────────────────────────

test('M4: an expired team invitation is rejected', function () {
    $token = Str::random(64);
    $team = Team::factory()->create();
    $member = User::factory()->create(['invitation_token' => null]);

    $team->members()->attach($member->id, [
        'role' => 'editor',
        'invitation_status' => 'pending',
        'invitation_token' => hash('sha256', $token),
        'invited_at' => now()->subDays(config('auth.invitation_ttl_days') + 1),
    ]);

    $this->get(route('teams.acceptInvitation', ['token' => $token]))
        ->assertRedirect(route('login'));

    expect($team->members()->where('user_id', $member->id)->first()->pivot->invitation_status)
        ->toBe('pending');
});

test('M5: accepting a team invitation marks it accepted and signs the member in', function () {
    $token = Str::random(64);
    $team = Team::factory()->create();
    $member = User::factory()->create(['invitation_token' => null]);

    $team->members()->attach($member->id, [
        'role' => 'editor',
        'invitation_status' => 'pending',
        'invitation_token' => hash('sha256', $token),
        'invited_at' => now(),
    ]);

    $this->get(route('teams.acceptInvitation', ['token' => $token]))
        ->assertRedirect(route('teams.show', $team));

    $this->assertAuthenticatedAs($member);

    $pivot = $team->members()->where('user_id', $member->id)->first()->pivot;
    expect($pivot->invitation_status)->toBe('accepted')
        ->and($pivot->invitation_token)->toBeNull();
});

test('M4: a disabled account cannot be signed in via a team invitation link', function () {
    $token = Str::random(64);
    $team = Team::factory()->create();
    $member = User::factory()->create(['invitation_token' => null, 'is_disabled' => true]);

    $team->members()->attach($member->id, [
        'role' => 'editor',
        'invitation_status' => 'pending',
        'invitation_token' => hash('sha256', $token),
        'invited_at' => now(),
    ]);

    $this->get(route('teams.acceptInvitation', ['token' => $token]))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

// ── M6: AI settings must be validated in code, not only by the DB row ─────

/**
 * Force a setting row to exist with an EMPTY validation_rules column, which is
 * the case the old code let through unchecked. Some rows are inserted by
 * migrations already, so this has to overwrite rather than insert.
 */
function unruledSetting(string $key, string $value, string $type, string $group): void
{
    AISetting::updateOrCreate(
        ['key' => $key],
        [
            'value' => $value, 'type' => $type, 'group' => $group,
            'label' => $key, 'description' => '',
            'is_sensitive' => false, 'is_required' => false,
            'validation_rules' => [],
        ]
    );
}

test('M6: an unknown ai provider is rejected even when the row carries no rules', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    unruledSetting('ai.provider', 'ollama', 'string', 'general');

    $this->actingAs($admin)
        ->post(route('admin.ai.settings.update'), ['ai_provider' => 'evil-provider'])
        ->assertSessionHasErrors('ai_provider');

    expect(AISetting::where('key', 'ai.provider')->first()->value)->toBe('ollama');
});

test('M6: a base_url that is not an http url is rejected', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    unruledSetting('ai.ollama.base_url', 'http://localhost:11434', 'string', 'ollama');

    $this->actingAs($admin)
        ->post(route('admin.ai.settings.update'), ['ai_ollama_base_url' => 'file:///etc/passwd'])
        ->assertSessionHasErrors('ai_ollama_base_url');

    expect(AISetting::where('key', 'ai.ollama.base_url')->first()->value)->toBe('http://localhost:11434');
});

test('M6: an out of range temperature is rejected', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    unruledSetting('ai.openai.temperature', '0.1', 'float', 'openai');

    $this->actingAs($admin)
        ->post(route('admin.ai.settings.update'), ['ai_openai_temperature' => '97'])
        ->assertSessionHasErrors('ai_openai_temperature');

    expect(AISetting::where('key', 'ai.openai.temperature')->first()->value)->toBe(0.1);
});

test('M6: a valid provider change is still accepted', function () {
    $admin = User::factory()->create(['is_super_admin' => true]);
    unruledSetting('ai.provider', 'ollama', 'string', 'general');

    $this->actingAs($admin)
        ->post(route('admin.ai.settings.update'), ['ai_provider' => 'openai'])
        ->assertSessionHasNoErrors();

    expect(AISetting::where('key', 'ai.provider')->first()->value)->toBe('openai');
});
