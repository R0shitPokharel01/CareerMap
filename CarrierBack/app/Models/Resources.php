<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resources extends Model
{
    protected $fillable = [
        'phase_id',
        'title',
        'provider',
        'url',
        'type',
        'cost',
    ];

    public function phase()
    {
        return $this->belongsTo(Phases::class, 'phase_id');
    }
}
