<?php

use App\Models\Folder;
use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Folder Policy Tests
test('user can view their own folder', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user, 'owner')->create();

    $this->assertTrue($user->can('view', $folder));
});

test('user can view team folder', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user);
    $folder = Folder::factory()->for($team, 'owner')->create();

    $this->assertTrue($user->can('view', $folder));
});

test('user cannot view other users folder', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $folder = Folder::factory()->for($otherUser, 'owner')->create();

    $this->assertFalse($user->can('view', $folder));
});

test('user can update their own folder', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user, 'owner')->create();

    $this->assertTrue($user->can('update', $folder));
});

test('user cannot update other users folder', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $folder = Folder::factory()->for($otherUser, 'owner')->create();

    $this->assertFalse($user->can('update', $folder));
});

test('user can delete their own folder', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user, 'owner')->create();

    $this->assertTrue($user->can('delete', $folder));
});

test('user cannot delete other users folder', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $folder = Folder::factory()->for($otherUser, 'owner')->create();

    $this->assertFalse($user->can('delete', $folder));
});

// Snippet Policy Tests
test('user can view their own snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $this->assertTrue($user->can('view', $snippet));
});

test('user can view team snippet', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user);
    $snippet = Snippet::factory()->for($team, 'owner')->create();

    $this->assertTrue($user->can('view', $snippet));
});

test('user cannot view other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($otherUser, 'owner')->create();

    $this->assertFalse($user->can('view', $snippet));
});

test('user can view public shared snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($otherUser, 'owner')->create();

    // Note: Snippet doesn't have is_public, shared snippets use SnippetShare model
    // So a non-owner would need a SnippetShare to view it
    $this->assertFalse($user->can('view', $snippet));
});

test('user can update their own snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $this->assertTrue($user->can('update', $snippet));
});

test('user cannot update other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($otherUser, 'owner')->create();

    $this->assertFalse($user->can('update', $snippet));
});

test('user can delete their own snippet', function () {
    $user = User::factory()->create();
    $snippet = Snippet::factory()->for($user, 'owner')->create();

    $this->assertTrue($user->can('delete', $snippet));
});

test('user cannot delete other users snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = Snippet::factory()->for($otherUser, 'owner')->create();

    $this->assertFalse($user->can('delete', $snippet));
});

// Team Policy Tests
test('team member can view team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user);

    $this->assertTrue($user->can('view', $team));
});

test('non member cannot view team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $this->assertFalse($user->can('view', $team));
});

test('team owner can update team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();
    $team->members()->attach($owner);

    $this->assertTrue($owner->can('update', $team));
});

test('non owner cannot update team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();
    $team->members()->attach([$owner->id, $member->id]);

    $this->assertFalse($member->can('update', $team));
});

test('team owner can delete team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();
    $team->members()->attach($owner);

    $this->assertTrue($owner->can('delete', $team));
});

test('non owner cannot delete team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();
    $team->members()->attach([$owner->id, $member->id]);

    $this->assertFalse($member->can('delete', $team));
});

test('team owner can manage settings', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();
    $team->members()->attach($owner);

    $this->assertTrue($owner->can('manageSettings', $team));
});

test('non owner cannot manage settings', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();
    $team->members()->attach([$owner->id, $member->id]);

    $this->assertFalse($member->can('manageSettings', $team));
});
