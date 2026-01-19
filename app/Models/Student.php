<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'card_uid',
        'group',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'external_id', 'external_id');
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }
}

