<?php

namespace App\Service;

use App\Models\AuthenticationModel;

class SignUpService
{
    protected $authModel;

    public function __construct(AuthenticationModel $authModel)
    {
        $this->authModel = $authModel;
    }

    function createSignUp(string $username, string $inputHashedPassword): array {
        $returnvalues = [];

        if ($this->checkEmpty($username) || $this->checkEmpty($inputHashedPassword)) {
            $returnvalues["empty_input"] = "Please fill in all the fields";
            return $returnvalues;
        }

        if ($this->authModel->checkUsernameExistence($username)) {
            $returnvalues["username_exists"] = "Username already exists.";
            return $returnvalues;
        }

        if ($this->authModel->createNewUser($username, $inputHashedPassword)) {
            $returnvalues["signup_success"] = "Sign up successful";
        } else {
            $returnvalues["signup_failed"] = "Failed to create user.";
        }
        return $returnvalues;
    }

    function checkEmpty(string $value): bool {
        return empty($value);
    }
}