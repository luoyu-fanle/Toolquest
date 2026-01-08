<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthenticationModel extends Model
{
    use HasFactory;

    protected $table = 'logins'; // Verplicht omdat de tabel niet 'authentication_models' heet

    protected $fillable = [
        'username', 
        'password', // match met migratie
        'email', 
        'role'
    ];

    function createNewUser(string $username, string $hashedPassword, string $email): bool {
        
        $user = $this->create([
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email,
            'role' => 'user'
        ]);
        // Controleer of de gebruiker succesvol is aangemaakt
        return (bool)$user; 
    }
    function checkUsernameExistence(string $username):bool{
        return $this->where('username', $username)->exists();
    }

    function getHashedPasswordByUsername(string $username): string|null {
        $hash = $this->where('username', $username)->value('password');
        return $hash;
    }

    function getUserDataByUsername(string $username): array|null {
        $userData = $this->where('username', $username)->first();
        return $userData ? $userData->toArray() : null;
    }

    public function refreshTokens()
    {
        
        return $this->hasMany(RefreshTokenModel::class, 'user_id', 'id');
    }

    public function saveRefreshToken(int $userId, string $hashedToken, int $expiresAt): bool 
    {
        try {
            
            $token = RefreshTokenModel::create([
                'user_id' => $userId,
                'token' => $hashedToken,
                'expires_at' => date('Y-m-d H:i:s', $expiresAt), // Converteer timestamp naar DB-formaat
            ]);
            
            return (bool)$token;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    
}


