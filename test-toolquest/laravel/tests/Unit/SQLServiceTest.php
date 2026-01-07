<?php

use App\Service\SQLService;
use App\Models\AuthenticationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);
beforeEach(function () {

    $this->user = AuthenticationModel::create([
        
        'username' => 'testuser',
        'password' => 'password123',
        'email' => 'test@example.com',
        'role' => 'user'
    ]);
    AuthenticationModel::create([
        'username' => 'admin',
        'password' => 'adminpass',
        'email' => 'admin@example.com',
        'role' => 'admin'
    ]);


    $this->sqlService = new SQLService();
});


test('vulnerableQuery haalt een gebruiker op met een geldig ID', function () {
    $result = $this->sqlService->vulnerableQuery('1');
    
    expect($result)->toBeArray()
        ->and($result[0]->username)->toBe('testuser');
});

test('vulnerableQuery is gevoelig voor SQL injectie (OR 1=1)', function () {
    $result = $this->sqlService->vulnerableQuery('1 OR 1=1');

    expect($result)->toHaveCount(2);
});

test('safeQuery haalt een gebruiker op met een geldig ID', function () {
    $result = $this->sqlService->safeQuery(1);
    
    expect($result)->toBeArray()
        ->and($result[0]->username)->toBe('testuser');
});

test('safeQuery blokkeert SQL injectie (geeft geen resultaat of gooit fout)', function () {
    $result = $this->sqlService->safeQuery('1 OR 1=1');

    expect($result)->toBeArray()
        ->toHaveCount(0); // Geen resultaten gevonden omdat de string veilig is behandeld
});