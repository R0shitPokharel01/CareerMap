<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Careers extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'approvedBy'
    ];
}
