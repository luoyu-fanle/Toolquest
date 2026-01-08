<?php

namespace App\Service;

use App\Models\AuthenticationModel;
use App\Service\JWTService;
class LoginService
{
    protected $authModel;
    protected $jwtService;

    public function __construct(AuthenticationModel $authModel, JWTService $jwtService = null)
    {
        $this->authModel = $authModel;
        $this->jwtService = $jwtService ?: new JWTService($authModel);
    }


    function createLogin(string $username, string $inputHashedPassword): array {
        $returnValues = [];
        
        if ($this->checkEmpty($username) || $this->checkEmpty($inputHashedPassword)) {
            $returnValues["empty_input"] = "Please fill in all the fields";
            return $returnValues;
        }

        if (!$this->checkCredentials($username, $inputHashedPassword)) {
            $returnValues["invalid_credentials"] = "Invalid username or password.";
            return $returnValues;
        }

        $token = $this->checkTokens($username);

        if (!$token) {
            return ["login_failed" => "Failed to create tokens."];
        }
        
        return [
            "access_token" => $token, 

        ];
    }

    function checkTokens (string $username): string|false {
        // Implement token validation logic here
        $userData = $this->authModel->getUserDataByUsername($username);
        
        if ($userData) {
            $jwtTokenResult = $this->jwtService->makeJwtToken((int)$userData['id'], $username, (string)$userData['role']);
            $refreshTokenResult = $this->jwtService->makeRefreshToken((int)$userData['id']);
            if ($jwtTokenResult !== false && $refreshTokenResult === true) {
                return $jwtTokenResult;
            }

        }
        return false;
    }
    function checkEmpty(string $value): bool {
        return empty($value);
    }

    function checkCredentials(string $username, string $inputHashedPassword): bool {
        if($this->authModel->checkUsernameExistence($username)) {
            if ($this->checkPasswords($username, $inputHashedPassword)) {
                return true;
            }
            return false;
        }else{
            return false;
        }
    }
    public function checkPasswords(string $username, string $inputHashedPassword): bool {
        $storedHash = $this->authModel->getHashedPasswordByUsername($username);
        if ($storedHash === null) {
            return false;
        }
        return hash_equals($storedHash, $inputHashedPassword);
    }
    
}
