<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievements extends Model
{
    protected $fillable =[
        'title','description','icon'
    ];

    public function userAchievements(){
        return $this->hasMany(UserAchievements::class);
    }
}
