<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\LoginService;

class AppLogInController extends Controller
{

    function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    function logIn(Request $request){

        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);
        $username = $validatedData['username'];
        $password = $validatedData['password'];

        $result = $this->loginService->createLogIn($username, $password);
        if (isset($result['login_success'])) {
            return redirect('/profile')->with('success', $result['login_success']);
        } else {
            $errorMessage = $result['invalid_credentials'] ?? $result['empty_input'] ?? 'An unknown error occurred.';
            return redirect('/login')->withInput()->withErrors(['login_error' => $errorMessage]);
        }
        
    }


}

