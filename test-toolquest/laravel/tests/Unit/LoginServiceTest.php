<?php



use App\Service\LoginService;
use App\Models\AuthenticationModel;
use App\Service\JWTService;
use Mockery as M;

    beforeEach(function () {
        $this->authModelMock = M::mock(AuthenticationModel::class);
        
        $this->jwtServiceMock = M::mock(JWTService::class);
        
        $this->loginService = new LoginService($this->authModelMock, $this->jwtServiceMock);
    });

    afterEach(function () {
        M::close();
    });
    // =================================================================
    // 2. Testen van Individuele Logica
    // =================================================================

    // =================================================================
    // 2.1 Testen van checkEmpty
    // =================================================================

    test('checkEmpty function returns true for an empty string', function () {
        // Arrange (voorbereiden)
        $service = $this->loginService;
        
        // Act (uitvoeren)
        $result = $service->checkEmpty("");
        
        // Assert (controleren)
        expect($result)->toBeTrue();
    });

    test('checkEmpty function returns false for a non-empty string', function () {
        $service = $this->loginService;
        $result = $service->checkEmpty("test");
        expect($result)->toBeFalse();
    });

    // =================================================================
    // 2.2 Testen van checkPasswords
    // =================================================================
    test('checkpasswords returns true for matching passwords', function () {
        $service = $this->loginService;
        $username = 'testuser';
        $storedHash = 'a_secure_and_matching_hash_123'; 
        $inputHash = 'a_secure_and_matching_hash_123';
        $this->authModelMock->shouldReceive('getHashedPasswordByUsername')->once()->with($username)->andReturn($storedHash);
        $result = $service->checkPasswords($username, $inputHash);

        expect($result)->toBeTrue();

    });

    test('checkpasswords returns false for non-matching passwords', function () {
        $service = $this->loginService;
        $username = 'testuser';
        $storedHash = 'a_secure_and_matching_hash_123'; 
        $inputHash = 'wrong_password';
        $this->authModelMock->shouldReceive('getHashedPasswordByUsername')->once()->with($username)->andReturn($storedHash);
        $result = $service->checkPasswords($username, $inputHash);

        expect($result)->toBeFalse();
    });

    // =================================================================
    // 2.3 Testen van checkCredentials
    // =================================================================
    test('checkCredentials returns false for non-existing username', function () {
        $this->authModelMock->shouldReceive('checkUsernameExistence')->once()->andReturnFalse();
        
        $result = $this->loginService->checkCredentials('nonexistentuser', 'any_password');
        expect($result)->toBeFalse();
    });

    test ('checkCredentials returns false for existing username but wrong password', function () {
        $this->authModelMock->shouldReceive('checkUsernameExistence')->once()->andReturnTrue();
        
        $this->loginService = M::mock(LoginService::class, [$this->authModelMock])->makePartial();
        $this->loginService->shouldReceive('checkPasswords')->once()->andReturnFalse(); 
        
        $result = $this->loginService->checkCredentials('testuser', 'wrong_password');
        
        expect($result)->toBeFalse();
    });

    test('checkCredentials returns true for valid username and password', function () {
        $this->authModelMock->shouldReceive('checkUsernameExistence')->once()->andReturnTrue();
        
        // Mock de checkPasswords om TRUE terug te geven (geldig wachtwoord)
        $this->loginService = M::mock(LoginService::class, [$this->authModelMock])->makePartial();
        $this->loginService->shouldReceive('checkPasswords')->once()->andReturnTrue(); 
        
        $result = $this->loginService->checkCredentials('validuser', 'correct_password');
        
        expect($result)->toBeTrue();
    });
    // =================================================================
    // 2.4 Testen van checkTokens
    // =================================================================  

    test('checkTokens returns false if user data is not found', function () {
        $this->authModelMock->shouldReceive('getUserDataByUsername')->once()->with('unknownuser')->andReturn(null);
        
        $result = $this->loginService->checkTokens('unknownuser');
        
        expect($result)->toBeFalse();
    });

    test('checkTokens returns false if token creation fails', function () {
        $userData = ['id' => 1, 'username' => 'testuser', 'roles' => 'user'];
        $this->authModelMock->shouldReceive('getUserDataByUsername')->once()->with('testuser')->andReturn($userData);
        
        $this->jwtServiceMock->shouldReceive('makeJwtToken')->once()->andReturn(null); // Simuleer mislukking
        $this->jwtServiceMock->shouldReceive('makeRefreshToken')->once()->andReturn(false); // Simuleer mislukking
        
        $result = $this->loginService->checkTokens('testuser');
        
        expect($result)->toBeFalse();
    });

    test('checkTokens returns true if token creation succeeds', function () {
        $userData = ['id' => 1, 'username' => 'testuser', 'roles' => 'user'];
        $this->authModelMock->shouldReceive('getUserDataByUsername')->once()->with('testuser')->andReturn($userData);
        
        $this->jwtServiceMock->shouldReceive('makeJwtToken')->once()->andReturn('valid.jwt.token');
        $this->jwtServiceMock->shouldReceive('makeRefreshToken')->once()->andReturn(true);
        
        $result = $this->loginService->checkTokens('testuser');
        
        expect($result)->toBeTrue();
    });

    // =================================================================
    // 3. Testen van de Hoofdlogica: createLogin
    // =================================================================

    test('createLogin returns empty_input error for empty username', function () {
        $username = '';
        $password = 'some_password';
        
        $result = $this->loginService->createLogin($username, $password);
        
        expect($result)->toHaveKey('empty_input')->and($result['empty_input'])->toBe('Please fill in all the fields');
    });

    test('createLogin returns invalid_credentials error for wrong credentials', function () {
        $username = 'testuser';
        $password = 'wrong_password';  

        $this->loginService = M::mock(LoginService::class, [$this->authModelMock])->makePartial();
        $this->loginService->shouldReceive('checkEmpty')->andReturnFalse();
        $this->loginService->shouldReceive('checkCredentials')->andReturnFalse();

        $result = $this->loginService->createLogin($username, $password);

        expect($result)->toHaveKey('invalid_credentials')->and($result['invalid_credentials'])->toBe('Invalid username or password.');
    });

    test('createLogin returns login_failed error when token creation fails', function () {
        $username = 'testuser';
        $password = 'correct_password';

        $this->loginService = M::mock(LoginService::class, [$this->authModelMock])->makePartial();
        $this->loginService->shouldReceive('checkEmpty')->andReturnFalse();
        $this->loginService->shouldReceive('checkCredentials')->andReturnTrue();
        $this->loginService->shouldReceive('checkTokens')->andReturnFalse();

        $result = $this->loginService->createLogin($username, $password);

        expect($result)->toHaveKey('login_failed')->and($result['login_failed'])->toBe('Failed to create tokens.');
    });

    test('createLogin succeeds and calls token creation functions', function () {
        $username = 'admin';
        $password = 'correct_password';
        $userData = ['id' => 101, 'username' => $username, 'roles' => 'admin'];
        
        $this->loginService = M::mock(LoginService::class, [$this->authModelMock])->makePartial();
        $this->loginService->shouldReceive('checkEmpty')->andReturnFalse();
        $this->loginService->shouldReceive('checkCredentials')->andReturnTrue();
        $this->loginService->shouldReceive('checkTokens')->andReturnTrue();
        
        $result = $this->loginService->createLogin($username, $password);
        
        expect($result)->toHaveKey('login_success')->and($result)->not->toHaveKey('login_failed');

    });

