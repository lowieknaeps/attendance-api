<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TodayStatusDonut extends ChartWidget
{
    protected static ?string $heading = 'Aanwezigheden vandaag';
    protected static ?string $pollingInterval = '30s'; // auto-refresh (optioneel)

    protected function getData(): array
    {
        $today = Carbon::today();

        $rows = Attendance::query()
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->whereDate('arrived', $today)
            ->groupBy('status')
            ->pluck('cnt', 'status'); // ['present' => 10, 'late' => 3, 'absent' => 2]

        $labels = ['present','late','absent'];
        $data   = array_map(fn($s) => (int) ($rows[$s] ?? 0), $labels);

        return [
            'labels' => ['Aanwezig', 'Te laat', 'Afwezig'],
            'datasets' => [[
                'label' => 'Vandaag',
                'data'  => $data,
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
