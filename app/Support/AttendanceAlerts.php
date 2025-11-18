<?php

namespace App\Support;

use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceAlerts
{
    /**
     * Controleer globale drempels voor vandaag.
     * Retourneert een array met “gebeurtenissen” die zijn overschreden.
     */
    public static function evaluateDaily(): array
    {
        $now   = Carbon::now();
        $start = $now->copy()->startOfDay();
        $end   = $now->copy()->endOfDay();

        $thresholds = config('attendance.thresholds');

        // Totaal AFWEZIG vandaag
        $absentToday = Attendance::query()
            ->whereBetween('arrived', [$start, $end])
            ->where('status', 'absent')
            ->count();

        $events = [];

        if ($absentToday >= ($thresholds['total_absent_today'] ?? PHP_INT_MAX)) {
            $msg = "Aantal afwezigen vandaag is: {$absentToday}";
            $events[] = [
                'type' => 'total_absent_today',
                'value' => $absentToday,
                'message' => $msg,
            ];
            self::notify($msg, danger: true);
        }

        return $events;
    }

    public static function evaluateForStudent(int|string $externalId): array
    {
        $now   = Carbon::now();
        $start = $now->copy()->startOfDay();
        $end   = $now->copy()->endOfDay();

        $thresholds = config('attendance.thresholds');

        $lateToday = Attendance::query()
            ->where('external_id', $externalId)
            ->whereBetween('arrived', [$start, $end])
            ->where('status', 'late')
            ->count();

        $late7Days = Attendance::query()
            ->where('external_id', $externalId)
            ->where('arrived', '>=', $now->copy()->subDays(7))
            ->where('status', 'late')
            ->count();

        $studentName = Attendance::query()
            ->where('external_id', $externalId)
            ->latest('arrived')
            ->value('name') ?? "Student {$externalId}";

        $events = [];

        if ($late7Days >= ($thresholds['student_late_7days'] ?? PHP_INT_MAX)) {
            $msg = "{$studentName} was in de laatste 7 dagen {$late7Days}× te laat.";
            $events[] = [
                'type' => 'student_late_7days',
                'student_id' => $externalId,
                'value' => $late7Days,
                'message' => $msg,
            ];
            self::notify($msg, warning: true);
        }

        return $events;
    }

    /**
     * Stuur melding naar Filament (indien beschikbaar) en log altijd.
     */
    // app/Support/AttendanceAlerts.php
    protected static function notify(string $message, bool $warning = false, bool $danger = false): void
    {
        \Log::info('[AttendanceAlerts] ' . $message);

        if (class_exists(\Filament\Notifications\Notification::class)) {
            $n = \Filament\Notifications\Notification::make()
                ->title('Attendance Alert')
                ->body($message);

            if ($danger) { $n->danger(); }
            elseif ($warning) { $n->warning(); }
            else { $n->success(); }

            // 1) Als je in het panel zit en ingelogd: toon toast + bewaar voor die user
            if (auth()->check() && !app()->runningInConsole() && !request()->expectsJson()) {
                $n->send();                       // toast
                $n->sendToDatabase(auth()->user()); // ook bewaren
                return;
            }

            // 2) API/CLI pad: stuur naar admins (of alle users)
            $recipients = \App\Models\User::query()
                ->when(\Schema::hasColumn('users','is_admin'), fn($q) => $q->where('is_admin', true))
                ->get();

            if ($recipients->isNotEmpty()) {
                $n->sendToDatabase($recipients);
            }
        }
    }

}
