<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;   
use Illuminate\Support\Carbon;


class AttendanceSession extends Model
{
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Course::class,
            'course_external_id', 
            'external_id'        
        );
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    protected static function booted()
    {
        static::creating(function ($session) {

            $session->start_time = now()->format('H:i:s');

            $session->late_from = now()->addMinutes(15)->format('H:i:s');
        });
    }


    protected $fillable = [
        'teacher_id',
        'course_external_id',
        'started_at',
        'ended_at',
        'device_ip',
        'note',
        'start_time',
        'late_from',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];
}
