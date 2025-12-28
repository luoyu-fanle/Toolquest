<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VulnLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppSignUpController;
use App\Http\Controllers\AppLogInController;
use App\Http\Controllers\AdminController; // Zorg dat deze Controller bestaat!

Route::view('/signup', 'signup')->name('signup');
// Route::view('/profile/{id}', 'profile')->name('profile');
Route::view('/login', 'login')->name('login');

Route::get('/profile',[ProfileController::class, 'auth'])
    ->middleware('jwt.auth')
    ->name('profile');

Route::middleware('jwt.admin')->group(function () {
    
    Route::view('/admin', 'admin')->name('admin');
    Route::view('/admin/user', 'admin-user')->name('admin-user');
    Route::view('/admin/permissions', 'admin-permissions')->name('admin-permissions');
    Route::view('/admin/logs', 'admin-logs')->name('admin-logs');
    //hier moeten nog post reoutes komen voor admin acties    
});

Route::get('/', function () {
    return view('home');
});

Route::post('/api/signup', [AppSignUpController::class, 'signUp']);
Route::post('/api/login', [AppLogInController::class, 'logIn']);

//Route::post('/test-sql', [VulnLoginController::class, 'login']);
// Route::get('/profile/{id}', [ProfileController::class, 'show']);
// Route::post('/api/login', [VulnLoginController::class, 'login']);




