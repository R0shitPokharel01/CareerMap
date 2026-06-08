<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Careers extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'approvedBy'
    ];

    public function users(){
        return $this->belongsToMany(User::class);
    }
    public function phases(){
        return $this->hasMany(Phases::class);
    }
}
