<?php

use App\Service\SQLService;
use App\Models\AuthenticationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // We maken één gebruiker aan zodat de database niet leeg is
    AuthenticationModel::create([
        'username' => 'victim_user',
        'password' => bcrypt('secret'),
        'email' => 'victim@example.com',
        'role' => 'user'
    ]);
});

// --- TEST 1: OVERBOSE ROUTE (TOONT FOUTEN) ---
test('Overbose route toont profiel bij geldig ID', function () {
    $response = $this->get('/profile/overbose?id=1');

    $response->assertStatus(200);
    $response->assertViewIs('profile_sql');
    $response->assertViewHas('result');
    
});

test('overbose route toont database errors bij een syntax fout', function () {
$response = $this->get('/profile/overbose?id=1\'');

    $response->assertStatus(302); // Redirect naar home
    $response->assertSessionHasErrors(['SQL Error message ']);
    
    // We checken of de error lekkage bevat
    $errors = session('errors')->getBag('default')->get('SQL Error message ');
    expect($errors[0])->toContain("WHERE id = 1'");
    expect($errors[0])->toMatch('/unrecognized token|syntax error/i');
});

test('overbose route toont errormessage bij niet bestaand ID', function () {
    $this->withoutExceptionHandling();
    $response = $this->get('/profile/overbose?id=999'); // Gebruik een ID dat echt niet bestaat

    $response->assertRedirect(route('home'));
    $response->assertSessionHasErrors(['errormessage']);
    

});


test('overbose route toont meerdere gebruikers bij een succesvolle OR 1=1 injectie', function () {
    // Maak een tweede gebruiker
    AuthenticationModel::create([
        'username' => 'admin_user',
        'password' => bcrypt('topsecret'),
        'email' => 'admin@example.com',
        'role' => 'admin'
    ]);

    // Voer de aanval uit
    $response = $this->get(route('profile.overbose', ['id' => '1 OR 1=1']));

    $response->assertStatus(200);
    
    // We verwachten dat BEIDE usernames in de HTML van de pagina staan
    $response->assertSee('victim_user');
    $response->assertSee('admin_user');
});

// --- TEST 2: SILENT ROUTE (VERBERGT FOUTEN) ---

test('silent route verbergt technische database errors', function () {
    // Zelfde foute input
    $response = $this->get(route('profile.silent', ['id' => "1'"]));

    $response->assertStatus(302);
    
    // De silent route moet een generieke melding geven, NIET de SQL error
    $response->assertSessionHasErrors(['msg']);
    
    $errors = session('errors')->getBag('default')->get('msg');
    
    expect($errors[0])->toBe('Something went wrong')
        ->and($errors[0])->not->toContain('syntax error');
});

