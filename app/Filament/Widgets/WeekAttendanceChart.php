<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WeekAttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'Aanwezigheid – laatste 7 dagen';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $end   = Carbon::today();
        $start = (clone $end)->subDays(6);

        $labels = collect(range(0, 6))
            ->map(fn ($i) => $start->copy()->addDays($i)->toDateString())
            ->values();

        $rows = Attendance::query()
            ->selectRaw("DATE(COALESCE(arrived, created_at)) as d")
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'late'    THEN 1 ELSE 0 END) as late_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'absent'  THEN 1 ELSE 0 END) as absent_cnt")
            ->whereDate(DB::raw('DATE(COALESCE(arrived, created_at))'), '>=', $start)
            ->whereDate(DB::raw('DATE(COALESCE(arrived, created_at))'), '<=', $end)
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $present = [];
        $late    = [];
        $absent  = [];

        foreach ($labels as $d) {
            $present[] = (int) ($rows[$d]->present_cnt ?? 0);
            $late[]    = (int) ($rows[$d]->late_cnt    ?? 0);
            $absent[]  = (int) ($rows[$d]->absent_cnt  ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Aanwezig',
                    'data'  => $present,
                ],
                [
                    'label' => 'Te laat',
                    'data'  => $late,
                ],
                [
                    'label' => 'Afwezig',
                    'data'  => $absent,
                ],
            ],
            'labels' => $labels->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->all(),
        ];
    }
}
