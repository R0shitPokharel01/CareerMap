<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoadmapProgress extends Model
{
    protected $table = 'user_roadmap_progress';

    protected $fillable = [
        'user_id',
        'roadmap_id',
        'percent_complete',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}