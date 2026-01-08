<?php

use App\Service\SQLService;
use App\Models\AuthenticationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Service\JWTService;
use Firebase\JWT\JWT;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // We maken één gebruiker aan zodat de database niet leeg is
    $this->user = AuthenticationModel::create([
        'username' => 'test_user',
        'password' => 'password',
        'email' => 'test@example.com',
        'role' => 'user'
    ]);
        
    AuthenticationModel::create([
        'username' => 'admin_user',
        'password' => 'topsecret',
        'email' => 'admin@example.com',
        'role' => 'admin'
    ]);
    $this->disableCookieEncryption(['jwt', 'refresh_token']);
    $this->jwtService = app(JWTService::class);

});

test('profiel route weigert toegang zonder JWT cookie', function () {
    $response = $this->get('/profile');
    $response->assertRedirect(route('login')); 
    $response->assertSessionHasErrors(['auth' => 'Authentication required. Please log in.']);

});

test ('profiel route geeft toegang met geldige JWT cookie', function () {
    // Maak een JWT token voor de testgebruiker
    $payload = [
        'iat' => time(),
        'exp' => time() + 900,
        'user_id' => $this->user->id,
        'username' => $this->user->username,
        'roles' => $this->user->role
    ];

    $secretkey = config('jwt.secret'); ///van .env
    $paddedKey = str_pad($secretkey, 32, "\0");
    $token = JWT::encode($payload, $paddedKey, 'HS256');
    // dump($token);
    $response = $this->withCookie('jwt', $token)
                     ->get('/profile');
    // dd($response->status(), $response->headers->get('Location'));
    // $response->assertStatus(200);
    $response->assertViewIs('profile_jwt'); // Pas aan naar jouw view naam
    $response->assertSee('test_user');
});

test('profiel route weigert toegang met een ongeldig token', function () {
    $invalidToken = "dit.is.geen_geldig_token";

    $response = $this->withCookie('jwt', $invalidToken)
                     ->get('/profile');

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['auth' => 'Authentication required. Please log in.']);

});
