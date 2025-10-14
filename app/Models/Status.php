<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    // STAAT HIER DE TRUC:
    protected $fillable = [
        'student_id', 'course_id', 'present', 'occurred_at'
    ];
    

    protected $casts = [
        'present'     => 'boolean',
        'occurred_at' => 'datetime',
    ];
}

