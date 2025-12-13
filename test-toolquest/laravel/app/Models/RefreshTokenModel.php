<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshTokenModel extends Model
{
    use HasFactory;

    protected $table = 'tokens'; 

    protected $fillable = [
        'user_id',       // De ID van de gebruiker (Foreign Key)
        'token',         // De gehashte Refresh Token (sha256)
        'expires_at',    // Wanneer de token verloopt
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    //relatie naar user
    public function user()
    {
        return $this->belongsTo(AuthenticationModel::class, 'user_id', 'id');
    }


}
