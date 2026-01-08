<?php

namespace Tests\Unit;

use App\Service\SignUpService;
use App\Models\AuthenticationModel;
use Mockery as M;

    // =================================================================
    // 1. Voorbereiding: Mocking
    // =================================================================
    beforeEach(function () {
        $this->authModelMock = M::mock(AuthenticationModel::class);
        $this->signupService = M::mock(SignUpService::class, [$this->authModelMock])->makePartial();
        
    });

    afterEach(function () {
        M::close();
    });

    // =================================================================
    // 2. Testen van Individuele Functies
    // =================================================================  
    test('checkEmpty returns true for empty input', function () {
        $result = $this->signupService->checkEmpty('');
        expect($result)->toBeTrue();
    });

    test('checkEmpty returns false for non-empty input', function () {
        $result = $this->signupService->checkEmpty('not_empty');
        expect($result)->toBeFalse();
    });
    // =================================================================
    // 2.2 Testen van createSignUp
    // =================================================================
    test('createSignUp returns empty_input when username is empty', function () {
        $this->signupService->shouldReceive('checkEmpty')->with('')->andReturn(true);
        $result = $this->signupService->createSignUp('', 'password123', 'test@example.com');
        expect($result)->toHaveKey('empty_input')->and($result['empty_input'])->toBe('Please fill in all the fields');
    });
    
    test('createSignUp returns username_exists when username already exists', function () {
        $this->signupService->shouldReceive('checkEmpty')->andReturnFalse();
        $this->authModelMock->shouldReceive('checkUsernameExistence')->andReturnTrue();
        $result = $this->signupService->createSignUp('existing_user', 'password123', 'test@example.com');
        expect($result)->toHaveKey('username_exists')->and($result['username_exists'])->toBe('Username already exists.');
    });

    test('createSignUp returns signup_failed when user creation fails', function () {
        $this->signupService->shouldReceive('checkEmpty')->andReturnFalse();
        $this->authModelMock->shouldReceive('checkUsernameExistence')->andReturnFalse();
        $this->authModelMock->shouldReceive('createNewUser')->andReturnFalse();
        $result = $this->signupService->createSignUp('new_user', 'password123', 'test@example.com');
        expect($result)->toHaveKey('signup_failed')->and($result['signup_failed'])->toBe('Failed to create user.');
    });

    test('createSignUp returns signup_success when user is created successfully', function () {
        $this->signupService->shouldReceive('checkEmpty')->andReturnFalse();
        $this->authModelMock->shouldReceive('checkUsernameExistence')->andReturnFalse();
        $this->authModelMock->shouldReceive('createNewUser')->andReturnTrue();
        $result = $this->signupService->createSignUp('new_user', 'password123','test@example.com');
        expect($result)->toHaveKey('signup_success')->and($result['signup_success'])->toBe('Sign up successful');
    });