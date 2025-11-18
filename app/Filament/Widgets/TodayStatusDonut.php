<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TodayStatusDonut extends ChartWidget
{
    protected int|string|array $columnSpan = 1;
    protected function getMaxHeight(): string
    {
        return '320px';
    }
    protected static ?string $heading = 'Status vandaag';

    protected function getType(): string
    {
        return 'doughnut';
    }
    protected static ?array $options = [
    'scales' => [
        'x' => [
            'display' => false,
        ],
        'y' => [
            'display' => false,
        ],
    ],
];

    protected function getData(): array
    {
        $start = Carbon::today();
        $end   = Carbon::tomorrow();

        $present = Attendance::query()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('arrived', [$start, $end])
                  ->orWhere(function ($qq) use ($start, $end) {
                      $qq->whereNull('arrived')->whereBetween('created_at', [$start, $end]);
                  });
            })
            ->where('status', 'present')
            ->count();

        $late = Attendance::query()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('arrived', [$start, $end])
                  ->orWhere(function ($qq) use ($start, $end) {
                      $qq->whereNull('arrived')->whereBetween('created_at', [$start, $end]);
                  });
            })
            ->where('status', 'late')
            ->count();

        $absent = Attendance::query()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('arrived', [$start, $end])
                  ->orWhere(function ($qq) use ($start, $end) {
                      $qq->whereNull('arrived')->whereBetween('created_at', [$start, $end]);
                  });
            })
            ->where('status', 'absent')
            ->count();

        $data = [$present, $late, $absent];
        $total = max(array_sum($data), 1); 

        return [
            'labels' => ['Aanwezig', 'Te laat', 'Afwezig'],
            'datasets' => [[
                'label' => 'Status vandaag',
                'data' => $data,
                'backgroundColor' => [
                    'rgba(34, 197, 94, 0.9)',  
                    'rgba(234, 179, 8, 0.9)',  
                    'rgba(239, 68, 68, 0.9)',   
                ],
                'borderColor' => '#ffffff',
                'borderWidth' => 0.5,
                'hoverOffset' => 0.75,
            ]],
            'options' => [
                'cutout' => '1%',   
                'plugins' => [
                    'legend' => [
                        'display' => true,
                        'position' => 'bottom',
                        'labels' => [
                            'color' => '#111827',
                            'font' => ['size' => 3.5, 'weight' => '150'],
                            'boxWidth' => 2.5,
                            'padding' => 1.5,
                        ],
                    ],
                    'tooltip' => [
                        'enabled' => true,
                        'callbacks' => [
                            'label' => fn ($ctx) =>
                                $ctx['label'] . ': ' .
                                $ctx['parsed'] .
                                ' (' . number_format(($ctx['parsed'] / $total) * 100, 1) . '%)',
                        ],
                    ],
                ],
                'animation' => [
                    'duration' => 300,
                ],
            ],
        ];
    }
}
