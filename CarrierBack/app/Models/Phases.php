<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phases extends Model
{
    protected $fillable = [
        'career_id',
        'title',
        'description',
        'sequence_num',
        'duration_range',
        'skills'
    ];

    public function careers()
    {
        return $this->belongsTo(Careers::class, 'career_id');
    }
    public function resources()
    {
        return $this->hasMany(Resources::class, 'phase_id');
    }
}
