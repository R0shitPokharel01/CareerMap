<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phases extends Model
{
    protected $fillable = [
        'career_id',
        'sequence_num',
        'title',
        'description',
        'level',
        'duration_range',
        'skills',
        'milestone',
    ];

    protected $casts = [
        'skills' => 'array',
    ];

    public function career()
    {
        return $this->belongsTo(Careers::class, 'career_id');
    }

    public function resources()
    {
        return $this->hasMany(Resources::class, 'phase_id');
    }
}
