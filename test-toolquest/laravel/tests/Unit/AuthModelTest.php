<?php

use App\Models\AuthenticationModel;
use App\Models\RefreshTokenModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function(){
    
    $this->model = new AuthenticationModel();
    $this->user = AuthenticationModel::create([
        'username' => 'testuser',
        'password' => '123',
        'email' => 'test@example.com',
        'role' => 'user'
    ]);


});

test('createNewUser return true for creating user ', function () {
    
    $result = $this->model->createNewUser(
        'testuser2', 
        password_hash('secret', PASSWORD_BCRYPT), 
        'test@example.com', 
        'user'
    );

    expect($result)->toBeTrue();
    $this->assertDatabaseHas('logins', [
        'username' => 'testuser',
        'email' => 'test@example.com'
    ]);
});

test('createNewUser returns false when username is missing', function () {
    // We verwachten een exception omdat 'username' verplicht is in de DB
    $this->expectException(\Illuminate\Database\QueryException::class);

    $this->model->create([
        'email' => 'test@example.com',
        'password' => '123'
        // 'username' ontbreekt hier
    ]);
});

test('checkUsernameExistence returns true for finding user', function(){
    $result = $this->model->checkUsernameExistence('testuser');
    expect($result)->toBeTrue();

});

test('checkUsernameExistence returns false for not finding user', function(){
    $result = $this->model->checkUsernameExistence('testuser2');
    expect($result)->toBeFalse();
    
});

test('getHashedPasswordByUsername returns password', function(){

    $result = $this->model->getHashedPasswordByUsername('testuser');

    expect($result)->tobe('123');
});

test('getUserDataByUsername returns null for not finding username', function(){
    $result = $this->model->getUserDataByUsername('testuser2');
    expect($result)->toBeNull();
});

test('getUserDataByUsername returns array for finding username', function(){
    $result = $this->model->getUserDataByUsername('testuser');
    expect($result)->toHaveKey('username')->and($result['username'])->toBe('testuser');
});

test('saveRefreshToken return true for creating token ', function () {
    $expiresAt = time() + 3600;
    $result = $this->model->saveRefreshToken(
        $this->user->id, 
        hash('sha256', 'token'), 
        $expiresAt
    );

    expect($result)->toBeTrue();
    $this->assertDatabaseHas('tokens', ['user_id' => $this->user->id]);
});