<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievements extends Model
{
    protected $fillable=[
        'user_id','achievement_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function achievement(){
        return $this->belongsTo(Achievements::class);
    }
}
