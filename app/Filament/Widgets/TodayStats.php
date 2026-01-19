<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceSession;   


class TodayStats extends BaseWidget
{
    protected ?string $heading = 'Vandaag – overzicht';

    protected function getCards(): array
    {
        $teacherId = Auth::id();
        $session = AttendanceSession::query()
            ->where('teacher_id', $teacherId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $session) {
            return [
                Card::make('Aanwezig', 0)->color('success'),
                Card::make('Te laat', 0)->color('warning'),
                Card::make('Afwezig', 0)->color('danger'),
            ];
        }

        $base = Attendance::query()
            ->where('attendance_session_id', $session->id);

        $present = (clone $base)->whereRaw("LOWER(status) = 'present'")->count();
        $late    = (clone $base)->whereRaw("LOWER(status) = 'late'")->count();
        $absent  = (clone $base)->whereRaw("LOWER(status) = 'absent'")->count();

        $unique  = (clone $base)->distinct('external_id')->count('external_id');

        return [
            Card::make('Aanwezig', $present)
                ->description("Unieke studenten: {$unique}")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Card::make('Te laat', $late)
                ->description('Vandaag')
                ->color('warning'),

            Card::make('Afwezig', $absent)
                ->description('Vandaag')
                ->color('danger'),
        ];
    }
}
