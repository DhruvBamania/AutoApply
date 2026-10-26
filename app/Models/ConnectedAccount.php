<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectedAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'connection_type',
        'access_token',
        'refresh_token',
        'account_identifier',
        'password',
        'extra_data',
    ];

    protected $casts = [
        'extra_data' => 'array',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
