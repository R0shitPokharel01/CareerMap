<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resources extends Model
{
    protected $fillable = [
        'phase_id',
        'title',
        'link',
        'type'
    ];

    public function phases(){
        return $this->belongsTo(Phases::class);
    }
}
