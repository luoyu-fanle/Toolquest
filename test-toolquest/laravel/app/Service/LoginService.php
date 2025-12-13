<?php

namespace App\Http\Service;

use App\Models\AuthenticationModel;
use App\Service\JWTService;
class LoginService
{
    protected $authModel;

    public function __construct(AuthenticationModel $authModel)
    {
        $this->authModel = $authModel;
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

        if (!$this->checkTokens($username)) {
            $returnValues["login_failed"] = "Failed to create tokens.";
            return $returnValues;
        }

        $returnValues["login_success"] = "Login successful";
        return $returnValues;
    }

    function checkTokens (string $username): bool {
        // Implement token validation logic here
        $userData = $this->authmodel->getUserDataByUsername($username);
        
        if ($userData) {
            $userID = $userData['id'];
            $role = $userData['roles'];
            
            $jwtTokenResult = $this->jwtService->makeJwtToken($userID, $username, $role);
            $refreshTokenResult = $this->jwtService->makeRefreshToken();
            if ($jwtTokenResult !== null && $refreshTokenResult === true) {
                return true;
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
