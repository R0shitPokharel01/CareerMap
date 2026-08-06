<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usertaskprogress extends Model
{
    use HasFactory;

    protected $table = 'user_task_progress';

    protected $fillable = [
        'user_id',
        'roadmap_id',
        'task_id',
        // 'progress_percentage',
        'status',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function career()
    {
        return $this->belongsTo(
            Careers::class,
            'career_id'
        );
    }

    public function phase()
    {
        return $this->belongsTo(
            Phases::class,
            'task_id'
        );
    }
}
