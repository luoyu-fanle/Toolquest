<?php

use App\Models\AuthenticationModel;
use App\Service\JWTService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);
beforeEach(function(){
    $this->frontendHash = hash('sha256', 'password123');
    $this->user = AuthenticationModel::create([
        'username' => 'testuser',
        'password' => $this->frontendHash ,
        'email' => 'test@example.com',
        'role' => 'user'
    ]);

});

test('user can login and receive a valid jwt', function () {

    // 2. Doe een echt POST-verzoek naar je API
    $response = $this->postJson('/api/login', [
        'username' => 'testuser',
        'password' => $this->frontendHash ,
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'access_token',
                 'user'
             ])
             ->assertJson(['user' => 'testuser']);

    //  check Cookies
    $response->assertCookie('jwt');
    $response->assertCookie('refresh_token');
});

test('user falied to login with invalid chredentials', function(){
    $response = $this->postJson('/api/login', [
        'username' => 'testuser',
        'password' => 'incorrect_password',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['message'])
             ->assertJson(['message' => 'Invalid username or password.']);
});

test('user falied to login with empty input', function(){
    $response = $this->postJson('/api/login', [
        'username' => '',
        'password' => 'random_password',
    ]);
    $response->assertStatus(422);
    // $response->assertStatus(200)
    //          ->assertJsonStructure(['message'])
    //          ->assertJson(['message' => 'Please fill in all the fields']);
});
