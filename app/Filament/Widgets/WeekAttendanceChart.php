<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;

class WeekAttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'Aanwezigheid – laatste 7 dagen';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $teacherId = Auth::id();
        $session = AttendanceSession::where('teacher_id', $teacherId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $session) {
            return [
                'datasets' => [
                    ['label' => 'Aanwezig', 'data' => array_fill(0, 7, 0)],
                    ['label' => 'Te laat', 'data' => array_fill(0, 7, 0)],
                    ['label' => 'Afwezig', 'data' => array_fill(0, 7, 0)],
                ],
                'labels' => collect(range(0, 6))
                    ->map(fn($i) => Carbon::today()->subDays(6 - $i)->format('Y-m-d'))
                    ->all(),
            ];
        }

        $end   = Carbon::today();
        $start = (clone $end)->subDays(6);

        $labels = collect(range(0, 6))
            ->map(fn ($i) => $start->copy()->addDays($i)->toDateString())
            ->values();

        $rows = Attendance::query()
            ->where('attendance_session_id', $session->id)
            ->selectRaw("DATE(COALESCE(arrived, created_at)) as d")
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_cnt")
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $present = [];
        $late = [];
        $absent = [];

        foreach ($labels as $d) {
            $present[] = (int) ($rows[$d]->present_cnt ?? 0);
            $late[]    = (int) ($rows[$d]->late_cnt ?? 0);
            $absent[]  = (int) ($rows[$d]->absent_cnt ?? 0);
        }

        return [
            'datasets' => [
                ['label' => 'Aanwezig', 'data' => $present],
                ['label' => 'Te laat', 'data' => $late],
                ['label' => 'Afwezig', 'data' => $absent],
            ],
            'labels' => $labels->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->all(),
        ];
    }
}

