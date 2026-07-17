<?php
  namespace App\Models;

  use Illuminate\Database\Eloquent\Model;
  
  class UserAcheivements extends Model
  {
    protected $table = 'user_achivements';

    protected $fillable = ['user_id', 'achivement_id', 'earned_at'];

    protected $casts =['earned_at' => 'datetime'];

    public function achivement()
    {
        return $this ->belongsTo(Achivements::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
  }