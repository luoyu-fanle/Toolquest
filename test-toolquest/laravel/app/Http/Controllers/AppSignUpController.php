<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\SignUpService;

class AppSignUpController extends Controller
{
    function __construct(SignUpService $signUpService)
    {
        $this->signUpService = $signUpService;
    }

    function signUp(Request $request){

        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);
        $username = $validatedData['username'];
        $password = $validatedData['password'];

        $result = $this->signUpService->createSignUp($username, $password);
        if (isset($result['signup_success'])) {
            return redirect('/login')->with('success', $result['signup_success']);
        } else {
            $errorMessage = $result['username_exists'] ?? $result['empty_input'] ?? $result['signup_failed'] ?? 'An unknown error occurred.';
            return redirect('/signup')->withInput()->withErrors(['signup_error' => $errorMessage]);
        }
        
    }
}
