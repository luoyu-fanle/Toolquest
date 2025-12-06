<?php

namespace App\Http\Services;

use App\Models\AuthenticationModel;
class LoginService
{
    protected $authModel;

    public function __construct(AuthenticationModel $authModel)
    {
        $this->authModel = $authModel;
    }


    function createLogin(string $username, string $inputHashedPassword): array {
        $returnvalues = [];

        if ($this->checkEmpty($username) || $this->checkEmpty($inputHashedPassword)) {
            $returnvalues["empty_input"] = "Please fill in all the fields";
            return $returnvalues;
        }

        if (!$this->checkCredentials($username, $inputHashedPassword)) {
            $returnvalues["invalid_credentials"] = "Invalid username or password.";
            return $returnvalues;
        }

        $returnvalues["login_success"] = "Login successful";
        return $returnvalues;
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