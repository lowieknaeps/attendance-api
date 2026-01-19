<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TodayStatusDonut extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Status vandaag';

    protected function getMaxHeight(): string
    {
        return '320px';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected static ?array $options = [
        'scales' => [
            'x' => ['display' => false],
            'y' => ['display' => false],
        ],
    ];

    protected function getData(): array
    {
        $teacherId = Auth::id();
        $session = AttendanceSession::where('teacher_id', $teacherId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $session) {
            return [
                'labels' => ['Aanwezig', 'Te laat', 'Afwezig'],
                'datasets' => [[
                    'label' => 'Status vandaag',
                    'data' => [0, 0, 0],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.9)',
                        'rgba(234, 179, 8, 0.9)',
                        'rgba(239, 68, 68, 0.9)',
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 0.5,
                    'hoverOffset' => 0.75,
                ]],
            ];
        }

        $present = Attendance::where('attendance_session_id', $session->id)
            ->where('status', 'present')
            ->count();

        $late = Attendance::where('attendance_session_id', $session->id)
            ->where('status', 'late')
            ->count();

        $absent = Attendance::where('attendance_session_id', $session->id)
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
                'animation' => ['duration' => 300],
            ],
        ];
    }
}
