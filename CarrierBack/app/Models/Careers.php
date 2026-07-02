<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Careers extends Model
{
    //
    protected $fillable = [
        'title',
        'slug',
        'difficulty',
        'description',
        'salary_range',
        'category',
        'salary_period',
        'status',
        'duration',
        'skills',
        'demand',
        'reviewed_by',
        'is_published'
    ];
    protected $casts = [
        'skills' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function phases()
    {
        return $this->hasMany(Phases::class);
    }
}
