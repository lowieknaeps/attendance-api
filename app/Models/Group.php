<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['external_id','code','name'];

    public function students()
    {
        return $this->belongsToMany(
            \App\Models\Student::class,
            'course_user',
            'course_id',
            'user_id',
        );
    }
}
