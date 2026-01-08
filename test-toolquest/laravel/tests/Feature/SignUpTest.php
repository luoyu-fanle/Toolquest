<?php

use App\Models\AuthenticationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);
beforeEach(function(){
AuthenticationModel::create([
        'username' => 'existinguser',
        'password' => bcrypt('password123'),
        'email' => 'existing@example.com',
        'role' => 'user'
    ]);

});

test('user can signup and receive a success message', function () {
    // 2. Doe een echt POST-verzoek naar je API
    $response = $this->postJson('/api/signup', [
        'username' => 'newuser',
        'password' => bcrypt('password123'),
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['message'])
             ->assertJson(['message' => 'Sign up successful']);
});

test('user failed to signup because of existing username', function () {
    // 2. Doe een echt POST-verzoek naar je API
    $response = $this->postJson('/api/signup', [
        'username' => 'existinguser',
        'password' => bcrypt('password123'),
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['message'])
             ->assertJson(['message' => 'Username already exists.']);
});

test('user falied to signup with empty input', function(){
    $response = $this->postJson('/api/signup', [
        'username' => '',
        'password' => 'random_password',
        'email' => 'test@example.com',
    ]);
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['username']);
    // $response->assertStatus(200)
    //          ->assertJsonStructure(['message'])
    //          ->assertJson(['message' => 'Please fill in all the fields']);
});