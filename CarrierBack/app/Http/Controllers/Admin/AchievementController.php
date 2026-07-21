<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'color',
        'type',
        'condition',
        'points',
        'is_active',
    ];

    protected $casts = [
        'condition' => 'array',
        'is_active' => 'boolean',
        'points'    => 'integer',
    ];

    // All users who earned this achievement
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot('earned_at')
                    ->withTimestamps();
    }

    // Only return active achievements
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}