<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthenticationModel extends Model
{
    protected $fillable = [
        'username', 
        'user_password' 
    ];

    function createNewUser(string $username, string $hashedPassword): bool {
        
        $user = $this->create([
            'username' => $username,
            'user_password' => $hashedPassword,
        ]);
        // Controleer of de gebruiker succesvol is aangemaakt
        return (bool)$user; 
    }
    function checkUsernameExistence(string $username):bool{
        return $this->where('username', $username)->exists();
    }

    function getHashedPasswordByUsername(string $username): string {
        $hash = $this->where('username', $username)->value('user_password');
        return $hash;
    }
}
