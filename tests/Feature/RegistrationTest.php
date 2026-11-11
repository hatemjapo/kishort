<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can register', function () {
    config(['app.registration_enabled' => true]);

    $response = $this->postJson('/api/register', [
        'name'     => 'TestUser',
        'email'    => 'test@test.at',
        'password' => 'password123',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['token', 'user']);

    $this->assertDatabaseHas('users', [
        'email' => 'test@test.at',
    ]);
});

test('registration fails with a too short password', function () {
    config(['app.registration_enabled' => true]);

    $response = $this->postJson('/api/register', [
        'name'     => 'TestUser',
        'email'    => 'test@test.at',
        'password' => 'aaa',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('registration is deactivated', function() {
    config(['app.registration_enabled' => false]);

    $response = $this->postJson('/api/register', [
        'name'     => 'TestUser',
        'email'    => 'test@test.at',
        'password' => 'password123',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('users', ['email' => 'test@test.at']);
});
