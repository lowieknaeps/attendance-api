<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'teacher_id',  
        'code',
        'name',
        'external_id',  
    ];
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_course', 'course_id', 'teacher_id');
    }

}
