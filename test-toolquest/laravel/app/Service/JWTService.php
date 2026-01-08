<?php

namespace App\Service;

use App\Models\AuthenticationModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cookie;


class JWTService
{
    protected $authModel;

    public function __construct(AuthenticationModel $authModel)
    {
        $this->authModel = $authModel;
    }

    function makeJwtToken(int $userID, string $username, string $role): string|false {
        $secretkey = config('jwt.secret'); ///van .env
        // dump ($secretkey);
        $paddedKey = str_pad($secretkey, 32, "\0");
        $issuedAt = time();
        $expiresAt = $issuedAt + 900; // 15 minuten

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'user_id' => $userID,
            'roles'=> $role,
            'username'=> $username
        ];
        try{
            $jwt = JWT::encode($payload, $paddedKey, 'HS256');
            // dump($jwt);
            // dump("JWT Error: " . $e->getMessage());
            cookie()->queue('jwt', $jwt, 15, '/', null, true, true, false, 'Strict');
            return $jwt;
        }catch (\Exception $e) {
            dump("JWT Error: " . $e->getMessage());
            return false;
        }

    }
    
    function makeRefreshToken(int $userID): bool {
        $issuedAt = time();
        $expiresAt = $issuedAt + 28800; ///8 uur
        $refreshToken = bin2hex(random_bytes(64));

        $saveTokenResult = $this->authModel->saveRefreshToken($userID, hash('sha256', $refreshToken), $expiresAt);
        if ($saveTokenResult === true) {
            try{
                cookie()->queue('refresh_token', $refreshToken, 480, '/', null, true, true, false, 'Strict');
                return true;
            }catch (\Exception $e) {
                return false;
            }
        }
        return false ;
    }

    // function sendCookie($token, string $name, int $date):bool {
    //     $result = setcookie($name, $token, [
    //         'path' => '/',
    //         'httponly' => true,
    //         'secure' => true,
    //         'samesite' => 'Strict',
    //         'max-age' => $date, // 8 uur/ 15min
    //     ]);
    //     if(!$result){
    //         return false;
    //     }
    //     return true;
    // }
    
    //////////////////////////////////////////
    //////////Weak key verify/////////////////
    //////////////////////////////////////////
    function verifyJwtTokenWeakKey(string $jwtToken): array|null {

        try {
             $secretkey = config('jwt.secret');
             $paddedKey = str_pad($secretkey, 32, "\0");
            $decoded = JWT::decode($jwtToken, new Key($paddedKey, 'HS256'));
            return (array)$decoded; // Token is valid
        } catch (\Exception $e) {
            return null; // Token is invalid
        }
    }

    //////////////////////////////////////////
    //////////arbitrary signatures////////////
    //////////////////////////////////////////
    function decodeRandomJwtToken($jwtToken): array|null {
        $parts = explode('.', $jwtToken);
        
        if (count($parts) !== 3) {
            return null;
        }

        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        
        if (!$this->validateExpiration($payload)) {
            return null; // Token is expired
        }

        return [
            'header' => $header,
            'payload' => $payload,
            'signature' => $parts[2] // Raw signature
        ];
    }

    function validateExpiration($payload): bool {
        $currentTime = time();
        if (isset($payload['exp']) && $payload['exp'] > $currentTime ) {
            return true; // Token is valid
        }
        return false; // Token is expirate
    }

}
