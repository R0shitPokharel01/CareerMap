<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStreak extends Model
{
    protected $table = 'user_streak'; 

    protected $fillable = [
        'user_id',
        'current_streak',
        'last_active_date', 
    ];

    protected $casts = ['last_active_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}