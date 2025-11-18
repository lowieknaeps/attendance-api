<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
class Attendance extends Model
{
    protected $table = 'attendances';
    protected $fillable = [
        'external_id',
        'name',
        'arrived',
        'status',
        'course_id',
        'group',
        'course_name',
        'room',
        'notes',
    ];
    public function teacher()
    {
        return $this->belongsTo(\App\Models\User::class, 'teacher_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'external_id');
    }
    public function scopeToday(Builder $q): Builder
    {
        return $q->where(function (Builder $q) {
            $q->whereDate('arrived', Carbon::today())
              ->orWhere(function (Builder $q) {
                  $q->whereNull('arrived')
                    ->whereDate('created_at', Carbon::today());
              });
        });
    }
    
    /**
     * Scope a query to records since a given date/time.
     *
     * @param Carbon|string $from
     */
    public function scopeSince(Builder $q, Carbon|string $from): Builder
    {
        $from = $from instanceof Carbon ? $from : Carbon::parse($from);
        
        return $q->where(function (Builder $q) use ($from) {
                $q->whereNotNull('arrived')->where('arrived', '>=', $from)
              ->orWhere(function (Builder $q) use ($from) {
                $q->whereNull('arrived')->where('created_at', '>=', $from);
              });
        });
    }
}

