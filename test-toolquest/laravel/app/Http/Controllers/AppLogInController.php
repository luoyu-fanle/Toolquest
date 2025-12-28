<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\LoginService;

class AppLogInController extends Controller
{

    function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    function logIn(Request $request){

        $validatedData = $request->validate([
            'username' => 'string|max:255',
            'password' => 'string|min:6',
        ]);
        $username = $validatedData['username'];
        $password = $validatedData['password'];

        $result = $this->loginService->createLogIn($username, $password);
        if (isset($result['access_token'])) {
            return response()->json([
                'message' => 'Login successful',
                'access_token' => $result['access_token'],
                'user' => $username
            ], 200);
        } else {
            $errorMessage = $result['invalid_credentials'] ?? $result['empty_input'] ?? $result['login_failed'] ?? 'An unknown error occurred.';
            return response()->json([
                'message' => $errorMessage
            ],200);
        }   
    }
}

