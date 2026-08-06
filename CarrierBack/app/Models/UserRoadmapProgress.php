<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoadmapProgress extends Model
{
    use HasFactory;

    protected $table = 'user_roadmap_progress';

    protected $fillable = [
        'user_id',
        'roadmap_id',
        'progress_percentage',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function career()
    {
        return $this->belongsTo(
            Careers::class,
            'roadmap_id'
        );
    }
}
