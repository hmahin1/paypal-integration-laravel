<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $fillable=[
        'user_id', 'user_token', 'revoke_or_status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }
}