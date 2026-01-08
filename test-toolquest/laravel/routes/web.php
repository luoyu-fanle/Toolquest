<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VulnLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppSignUpController;
use App\Http\Controllers\AppLogInController;
use App\Http\Controllers\Admin\AdminUserController; 
use App\Http\Controllers\SQLController;

Route::view('/signup', 'signup')->name('signup');
Route::view('/login', 'login')->name('login');


Route::get('/profile/overbose', [SQLController::class, 'getProfileOverbose'])->name('profile.overbose');
Route::get('/profile/silent', [SQLController::class, 'getProfileSilent'])->name('profile.silent');
Route::get('/profile',[ProfileController::class, 'auth'])
    ->middleware('jwt.auth')
    ->name('profile.jwt');

Route::middleware('jwt.admin')->group(function () {
    Route::get('/admin', [AdminUserController::class, 'index'])->name('admin');

    Route::post('/admin/user/create', [AdminUserController::class, 'create'])->name('admin.user.create');
    Route::put('/admin/user/edit/{id}', [AdminUserController::class, 'edit'])->name('admin.user.edit');
    Route::delete('/admin/user/delete/{id}', [AdminUserController::class, 'delete'])->name('admin.user.delete');

});

Route::get('/', function () {
    return view('home');
})->name('home');

Route::post('/api/signup', [AppSignUpController::class, 'signUp']);
Route::post('/api/login', [AppLogInController::class, 'logIn']);

//Route::post('/test-sql', [VulnLoginController::class, 'login']);
// Route::get('/profile/{id}', [ProfileController::class, 'show']);
// Route::post('/api/login', [VulnLoginController::class, 'login']);




