<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VulnLoginController;
use App\Http\Controllers\ProfileController;


Route::get('/', function () {
    return view('test-sql');
});

//Route::post('/test-sql', [VulnLoginController::class, 'login']);

Route::get('/profile/{id}', [ProfileController::class, 'show']);

// Route::post('/api/login', [VulnLoginController::class, 'login']);

Route::post('api/signup', [AppSignUpController::class, 'signUp']);

Route::post('api/login', [AppLogInController::class, 'logIn']);




