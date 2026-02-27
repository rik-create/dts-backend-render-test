<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRefreshToken extends Model
{

    protected $fillable = [
    'user_id',
    'refresh_token_hash',
    'selector',
    'expires_at'
];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
