<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['external_id', 'name', 'arrived', 'status'];
    protected $casts = ['arrived' => 'datetime'];
}

