<?php

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Group;   
class Attendance extends Model
{
    protected $fillable = [
        'external_id','name','arrived','status',
        // legacy kolommen laten staan:
        'course_id','course_name','group','room','notes',
        // nieuwe fk’s:
        'course_fk_id','group_fk_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_fk_id');
    }   
    public function groupRel(){ return $this->belongsTo(Group::class,   'group_fk_id'); }
}