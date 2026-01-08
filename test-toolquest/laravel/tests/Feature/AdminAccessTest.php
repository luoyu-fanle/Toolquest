<?php

use App\Models\AuthenticationModel;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->disableCookieEncryption(['jwt', 'refresh_token']);

    // Maak een normale gebruiker
    $this->user = AuthenticationModel::create([
        'username' => 'regular_user',
        'password' => 'password',
        'email' => 'user@example.com',
        'role' => 'user'
    ]);

    // Maak een admin gebruiker
    $this->admin = AuthenticationModel::create([
        'username' => 'admin_user',
        'password' => 'password',
        'email' => 'admin@example.com',
        'role' => 'admin'
    ]);
});


function createTestToken($user) {
    $payload = [
        'iat' => time(),
        'exp' => time() + 900,
        'user_id' => $user->id,
        'username' => $user->username,
        'roles' => $user->role, // Cruciaal voor CheckJwtAccess
    ];
    $paddedKey = str_pad(config('jwt.secret'), 32, "\0");
    return JWT::encode($payload, $paddedKey, 'HS256');
}

test('admin route is toegankelijk voor gebruikers met admin rol', function () {
    $token = createTestToken($this->admin);

    $response = $this->withCookie('jwt', $token)
                     ->get('/admin');

    $response->assertStatus(200);
    $response->assertViewIs('admin');
    $response->assertSee('admin_user');
});

test('admin route weigert toegang voor normale gebruikers (role: user)', function () {
    $token = createTestToken($this->user);

    $response = $this->withCookie('jwt', $token)
                     ->get('/admin');

    $response->assertRedirect('/');
    $response->assertSessionHasErrors(['auth' => 'Toegang geweigerd: Onvoldoende rechten.']);
});

test('normale gebruiker kan geen gebruiker verwijderen', function () {
    $token = createTestToken($this->user);
    
    $response = $this->withCookie('jwt', $token)
                     ->delete('/admin/user/delete/' . $this->admin->id);

    $response->assertRedirect('/');
    $response->assertSessionHasErrors(['auth' => 'Toegang geweigerd: Onvoldoende rechten.']);

    $this->assertDatabaseHas('logins', [
        'id' => $this->admin->id
    ]);
});

test('admin kan een gebruiker verwijderen', function () {
    $token = createTestToken($this->admin);
    
    $response = $this->withCookie('jwt', $token)
                     ->delete('/admin/user/delete/' . $this->user->id);

    $response->assertStatus(302); // Redirect back
    $this->assertDatabaseMissing('logins', [
        'id' => $this->user->id
    ]);
});

test ('admin kan een gebruiker editen', function () {
    $token = createTestToken($this->admin);

    $newData = [
        'username' => 'updated_user',
        'email' => 'updated@example.com',
        'role' => 'admin'
    ];  
    $response = $this->withCookie('jwt', $token)
                     ->put('/admin/user/edit/' . $this->user->id, $newData);
    $response->assertStatus(302); // Redirect back
    $response->assertSessionHasNoErrors();
    // $response->assertRedirect('/'); // Redirect back
    $this->assertDatabaseHas('logins', [
        'id' => $this->user->id,
        'username' => 'updated_user',
        'email' => 'updated@example.com',
        'role' => 'admin'
    ]);
});

test('admin kan nieuwe gebruiker aanmaken', function () {
    $token = createTestToken($this->admin);

    $newUserData = [
        'username' => 'new_user',
        'password' => hash('sha256', 'newpassword'),
        'email' => 'new@example.com',
        'role' => 'user'
    ];
    $response = $this->withCookie('jwt', $token)
                     ->post('/admin/user/create', $newUserData);
    $response->assertStatus(302); // Redirect back
    $this->assertDatabaseHas('logins', [
        'username' => 'new_user',
        'email' => 'new@example.com',
        'role' => 'user'
    ]);
});