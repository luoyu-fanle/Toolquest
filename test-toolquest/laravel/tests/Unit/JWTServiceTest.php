<?php



use App\Service\JWTService;
use App\Models\AuthenticationModel;
use Mockery as M;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config; 
use Firebase\JWT\Key; 
use Illuminate\Support\Facades\Cookie;

    uses(Tests\TestCase::class);
    beforeEach(function () {
        // We mocken de AuthenticationModel omdat we de DB NIET willen aanroepen.
        $this->authModelMock = M::mock(AuthenticationModel::class);
        $this->jwtService =  new JWTService($this->authModelMock);
        Config::set('jwt.secret', 'my_long_long_long_long_long_long_long_long_secret_key_1234567890');

    });

    afterEach(function () {
        M::close();
    });

    // =================================================================
    // 2. Testen van Individuele Logica
    // =================================================================

    // =================================================================
    // 2.1 Testen van validateExpirationToken
    // =================================================================

    test('validateExpiredToken returns false for expired token', function () {
        $payload =[
            'exp' => time() - 3600
        ];
        $result = $this->jwtService->validateExpiration($payload);
        expect($result)->toBeFalse();
    });

    test('validateExpiredToken returns false for token without exp field', function () {
        $payload =[
            'iat' => time() - 7200,
            'user_id' => '123',
            'roles'=> 'user',
            'username'=> 'testuser'
        ];
        $result = $this->jwtService->validateExpiration($payload);
        expect($result)->toBeFalse();
    });

    test('validateExpiredToken returns true for valid token', function () {
        $payload =[
            'exp' => time() + 3600
        ];
        $result = $this->jwtService->validateExpiration($payload);
        expect($result)->toBeTrue();
    });

    // =================================================================
    // 2.2 Testen van decodeRandomJwtToken
    // =================================================================    

    test('decodeRandomJwtToken returns null for invalid token', function () {
        $invalidToken = 'invalid.token';
        $result = $this->jwtService->decodeRandomJwtToken($invalidToken);
        expect($result)->toBeNull();
    });

    test('decodeRandomJwtToken return null for expired token', function () {
        $testjwtToken = 'jwt.token.key';
        $this->jwtService = M::mock(JWTService::class, [$this->authModelMock])->makePartial();
        $this->jwtService->shouldReceive('validateExpiration')->andReturnFalse();
        $result = $this->jwtService->decodeRandomJwtToken($testjwtToken);
        expect($result)->toBeNull();
    
    });

    test('decodeRandomJwtToken returns payload for valid token', function () {
        $payload = [
            'iat' => time() - 7200,
            'exp' => time() + 3600,
            'user_id' => '123',
            'roles'=> 'user',
            'username'=> 'testuser'
        ];
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        $testjwtToken = base64_encode(json_encode($header)) . '.' . base64_encode(json_encode($payload)) . '.signature';
        $this->jwtService = M::mock(JWTService::class, [$this->authModelMock])->makePartial();
        $this->jwtService->shouldReceive('validateExpiration')->andReturnTrue();
        $result = $this->jwtService->decodeRandomJwtToken($testjwtToken);
        expect($result['payload']['user_id'])->toBe('123')->and($result['signature'])->toBe('signature');    
    });
    // =================================================================
    // 2.3 Testen van verifyJwtTokenWeakKey
    // =================================================================

    test('verifyJwtTokenWeakKey returns null for invalid token', function () {
        $wrongSecret ='my_long_long_long_long_long_long_long_long_wrong_secret_key_1234567890';
        $invalidToken = JWT::encode(['user_id' => '1'], $wrongSecret, 'HS256');
        $result = $this->jwtService->verifyJwtTokenWeakKey($invalidToken);
        expect($result)->toBeNull();

    });

    test('verifyJwtTokenWeakKey returns payload for valid token', function () {
        $secretkey = Config::get('jwt.secret');
        $payload = [
            'iat' => time() - 7200,
            'exp' => time() + 3600,
            'user_id' => '123',
            'roles'=> 'user',
            'username'=> 'testuser'
        ];
        $jwtToken = JWT::encode($payload, $secretkey, 'HS256');
        $result = $this->jwtService->verifyJwtTokenWeakKey($jwtToken);
        expect($result)->toEqual($payload);
        expect($result)->toBeArray();
        expect($result['user_id'])->toBe('123');
    });

    // =================================================================
    // 2.4 Testen van makeRefreshToken 
    // =================================================================

    test('makeRefreshToken return true when genarating a token and stores it', function () {
        Cookie::shouldReceive('queue')
            ->once()
            ->with('refresh_token', Mockery::any(), 480, '/', null, true, true, false, 'Strict');

        $this->authModelMock->shouldReceive('saveRefreshToken')->andReturnTrue();
        $result = $this->jwtService->makeRefreshToken(123);
        expect($result)->toBeTrue();

    });

    test('makeRefreshToken return false when storing the token fails', function () {
        $this->authModelMock->shouldReceive('saveRefreshToken')->andReturnFalse();
        $result = $this->jwtService->makeRefreshToken(123);
        expect($result)->toBeFalse();
    });

    // =================================================================
    // 2.5 Testen van makeJwtToken 
    // =================================================================

    test('makeJwtToken return jwt token when successful', function () {
        $secretkey = Config::get('jwt.secret');
        Cookie::shouldReceive('queue')
            ->once()
            ->with('jwt', Mockery::any(), 15, '/', null, true, true, false, 'Strict');
        $result = $this->jwtService->makeJwtToken(123, 'testuser', 'user');

        expect($result)->toBeString();
        $decoded = JWT::decode($result, new Key($secretkey, 'HS256'));

        expect($decoded)->toHaveProperty('user_id')->and($decoded->user_id)->toBe(123)
                        ->and($decoded)->toHaveProperty('username')->and($decoded->username)->toBe('testuser')
                        ->and($decoded)->toHaveProperty('roles')->and($decoded->roles)->toBe('user');
    });