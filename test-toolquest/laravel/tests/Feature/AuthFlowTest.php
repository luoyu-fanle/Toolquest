<?php

use App\Models\AuthenticationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);
beforeEach(function(){
    $this->user = AuthenticationModel::create([
        'username' => 'testuser',
        'password' => bcrypt('password123'),
        'email' => 'test@example.com',
        'role' => 'user'
    ]);

});

test('user can signup and receive a success message', function () {

    // Doe een echt POST-verzoek naar je API
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    // 3. Check of de status 200 is en of er een token in de response zit
    $response->assertStatus(200)
             ->assertJsonStructure(['access_token', 'user']);

    // 4. Test of je met dat token een beveiligde route kunt bezoeken
    $token = $response->json('access_token');
    
    $profileResponse = $this->withToken($token)
                            ->getJson('/api/profile');

    $profileResponse->assertStatus(200)
                    ->assertJson(['email' => 'test@example.com']);
});