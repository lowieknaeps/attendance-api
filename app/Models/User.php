<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;
    public function courses()
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class, 'teacher_id');
    }

    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isTeacher(): bool { return $this->role === 'teacher'; }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'teacher'], true);
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'courses',     
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'courses' => 'array',
    ];
}
