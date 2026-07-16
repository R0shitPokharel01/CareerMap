<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Careers extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'category',
        'demand',
        'demand_reason',
        'salary_range',
        'salary_period',
        'salary_note',
        'duration',
        'skills',
        'prerequisites',
        'tools',
        'certifications',
        'career_paths',
        'reviewed_by',
        'is_published',
    ];

    protected $casts = [
        'skills' => 'array',
        'prerequisites' => 'array',
        'tools' => 'array',
        'certifications' => 'array',
        'career_paths' => 'array',
        'is_published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function phases()
    {
        return $this->hasMany(Phases::class, 'career_id');
    }
}
