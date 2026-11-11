<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can login', function () {
    User::factory()->create([
        'email'    => 'test@test.at',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'test@test.at',
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['token']);
});

test('a user cant login with wrong credentials', function() {
    User::factory()->create([
        'email'    => 'test@test.at',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'test@test.at',
        'password' => 'password456',
    ]);

    $response->assertStatus(422);
    $response->assertJsonMissing(['token']);
});
