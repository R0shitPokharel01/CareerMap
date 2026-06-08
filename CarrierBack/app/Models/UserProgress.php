<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    protected $fillable = ['user_id','career_id','phase_'];
    public function user(){
        return $this->belongsTo(User::class);

    }
}