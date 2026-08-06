<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievements extends Model
{
  protected $table = 'user_achievements';

  protected $fillable = [
    'user_id',
    'achievement_id',
    'earned_at',
  ];

  protected $casts = [
    'earned_at' => 'datetime',
  ];

  public function achievement()
  {
    return $this->belongsTo(
      Achivements::class,
      'achievement_id'
    );
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
