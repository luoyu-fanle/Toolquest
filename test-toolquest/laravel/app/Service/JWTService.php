<?php

namespace App\Http\Services;

use App\Models\AuthenticationModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class JWTService
{
    protected $authModel;

    public function __construct(AuthenticationModel $authModel)
    {
        $this->authModel = $authModel;
    }

    function makeJwtToken(string $userID, string $username, string $role): string {
        $secretkey = env('JWT_SECRET'); ///van .env
        $issuedAt = time();
        $expiresAt = $issuedAt + 900; // 15 minuten

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'user_id' => $userID,
            'roles'=> $role,
            'username'=> $username
        ];

        $jwt = JWT::encode($payload, $secretkey, 'HS256');
        $this->sendCookie($this->jwtToken, "jwt", 900);
        return $jwt;

    }
    
    function makeRefreshToken() {
        $issuedAt = time();
        $expiresAt = $issuedAt + 28800; ///8 uur
        $refreshToken = bin2hex(random_bytes(64));
        $saveTokenResult = $this->authModel->saveRefreshToken(expiresAt: $expiresAt);
        if ($saveTokenResult === true) {
            $this->sendCookie($this->refreshToken, "refresh_token", 28800);
        }
    }

    private function sendCookie($token, string $name, int $date) {
        setcookie($name, $token, [
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict',
            'max-age' => $date, // 8 uur/ 15min
        ]);
    }
    //////////////////////////////////////////
    //////////Weak key verify/////////////////
    //////////////////////////////////////////
    function verifyJwtTokenWeakKey(string $jwtToken): array {
        $secretkey = env('JWT_SECRET'); ///van .env

        try {
            $decoded = JWT::decode($jwtToken, new Key($secretkey, 'HS256'));
            return (array)$decoded; // Token is valid
        } catch (Exception $e) {
            return null; // Token is invalid
        }
    }

    //////////////////////////////////////////
    //////////arbitrary signatures////////////
    //////////////////////////////////////////
    function decodeRandomJwtToken($jwtToken){
        $parts = explode('.', $jwtToken);
        
        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$this->validateExpiration($header)) {
            return null; // Token is expired
        }
        return [
            'header' => $header,
            'payload' => $payload,
            'signature' => $parts[2] // Raw signature
        ];
    }

    function validateExpiration($header): bool {
        $currentTime = time();
        if (isset($header['exp']) && $header['exp'] < $currentTime) {
            return false; // Token is expired
        }
        return true; // Token is valid
    }

}
