<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTaskProgress extends Model
{
    protected $table = 'user_task_progress';

    protected $fillable = [
        'user_id',
        'task_id',
        'roadmap_id',
        'status',
        'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}