<?php

use App\Jobs\RecordClickJob;
use App\Models\Clicks;
use App\Models\User;
use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('not logged in and cant create Shortlink', function () {
    $response = $this->postJson('/api/links', [
        'target_url' => 'google.com',
    ]);

    $response->assertStatus(401);
});

test('logged in and cant create Shortlink with url not in right format', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/links', [
    'target_url' => 'google.com',
    ]);

    $response->assertStatus(422);
});

test('logged in and can create Shortlink', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/links', [
    'target_url' => 'https://google.com',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('short_links', [
        'target_url' => 'https://google.com',
        'user_id' => $user->id,
    ]);
});

test('user can update their own link', function() {
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create([
        'target_url' => 'https://old.com',
    ]);
    
    $response = $this->actingAs($user)->putJson("/api/links/{$link->id}",[
        'target_url' => 'https://new.com',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('short_links', [
        'id' => $link->id,
        'target_url' => 'https://new.com',
    ]);
});

test('user cant update someone elses link', function() {
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $link = ShortLink::factory()->for($owner)->create([
        'target_url' => 'https://old.com',
    ]);

    $response = $this->actingAs($user)->putJson("/api/links/{$link->id}",[
        'target_url' => 'https://new.com',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseHas('short_links', [
        'id' => $link->id,
        'target_url' => 'https://old.com',
    ]);
});

test('user can change is_active state', function() {
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create([
        'target_url' => 'https://old.com',
        "is_active" => false,
    ]);

    $this->assertDatabaseHas('short_links', [
        'id' => $link->id,
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)->patchJson("/api/links/{$link->id}/state",[
        'is_active' => true,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('short_links', [
        'id' => $link->id,
        'is_active' => true,
    ]);
});

test('user can delete their own link', function() {
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create([
        'target_url' => 'https://old.com',
    ]);
    
    $response = $this->actingAs($user)->deleteJson("/api/links/{$link->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('short_links', [
        'id'         => $link->id,
    ]);
});

test('user cant delete someone elses link', function() {
    $owner = User::factory()->create();
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($owner)->create([
        'target_url' => 'https://old.com',
    ]);
    
    $response = $this->actingAs($user)->deleteJson("/api/links/{$link->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('short_links', [
        'id'         => $link->id,
    ]);
});

test('url is redirecting to source', function() {
    Queue::fake();

    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create([
        'target_url' => 'https://old.com',
    ]);

    $response = $this->get("/{$link->code}");

    $response->assertRedirect('https://old.com');

    Queue::assertPushed(RecordClickJob::class);
});

test('unkown url is not redirecting', function() {
    $response = $this->get("/test");

    $response->assertStatus(404);
});

test('url is unusable and not redirecting', function() {
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create([
        'target_url' => 'https://old.com',
        "is_active" => false,
    ]);

    $response = $this->get("/{$link->code}");

    $response->assertStatus(410);
});

test('job writes a click to the database', function () {
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create();

    (new RecordClickJob(
        $link->id,
        'https://new.com',
        'TestAgent/1.0',
        '127.0.0.1',
    ))->handle();

    $this->assertDatabaseHas('clicks', [
        'short_link_id' => $link->id,
        'referrer'      => 'https://new.com',
        'user_agent'    => 'TestAgent/1.0',
        'ip'            => '127.0.0.1',
    ]);
});

test('get all urls from user', function() {
    $user = User::factory()->create();
    ShortLink::factory()->for($user)->count(2)->create();

    $response = $this->actingAs($user)->getJson('/api/links');

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
});

test('get all urls is getting the right urls', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    ShortLink::factory()->for($user1)->count(3)->create();
    ShortLink::factory()->for($user2)->count(2)->create();

    $response = $this->actingAs($user1)->getJson('/api/links');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');

    $response = $this->actingAs($user2)->getJson('/api/links');

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
});

test('get one shortlink from user', function() {
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create();

    $response = $this->actingAs($user)->getJson("/api/links/{$link->id}");

    $response->assertStatus(200);
});

test('user cant get one shortlink from another user', function() {
    $owner = User::factory()->create();
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($owner)->create();

    $response = $this->actingAs($user)->getJson("/api/links/{$link->id}");

    $response->assertStatus(403);
});

test('get shortlink stats', function() {
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($user)->create();

    Clicks::factory()->for($link)->count(3)->create();

    $response = $this->actingAs($user)->getJson("/api/links/{$link->id}/stats");

    $response->assertStatus(200);
    $response->assertJson([
        'total_clicks' => 3,
    ]);
});

test('user cant get shortlink stats from another user', function() {
    $owner = User::factory()->create();
    $user = User::factory()->create();
    $link = ShortLink::factory()->for($owner)->create();

    Clicks::factory()->for($link)->count(3)->create();

    $response = $this->actingAs($user)->getJson("/api/links/{$link->id}/stats");

    $response->assertStatus(403);
});