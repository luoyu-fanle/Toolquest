<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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


