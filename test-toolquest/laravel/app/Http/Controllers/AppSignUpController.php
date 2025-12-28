<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\SignUpService;

class AppSignUpController extends Controller
{
    function __construct(SignUpService $signUpService)
    {
        $this->signUpService = $signUpService;
    }

    function signUp(Request $request){

        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'email'    => 'required|string|max:255',
        ]);

        $username = $validatedData['username'];
        $password = $validatedData['password'];
        $email = $validatedData['email'];

        $result = $this->signUpService->createSignUp($username, $password, $email);
        if (isset($result['signup_success'])) {
            return response()->json(['message' => $result['signup_success']],200);
        } else {
            $errorMessage = $result['username_exists'] ?? $result['empty_input'] ?? $result['signup_failed'] ?? 'An unknown error occurred.';
            return response()->json([
                'message' => $errorMessage
            ],200);
        }
        
    }
}
