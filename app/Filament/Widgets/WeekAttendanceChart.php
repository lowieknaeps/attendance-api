<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class WeekAttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'Aanwezigheden laatste 7 dagen';
    protected function getType(): string { return 'bar'; }

    protected function getData(): array {
        $labels = [];
        $on = []; $late = []; $abs = [];
        for ($i=6; $i>=0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->format('d-m');
            $on[]   = Attendance::whereDate('created_at',$day)->where('status','on_time')->count();
            $late[] = Attendance::whereDate('created_at',$day)->where('status','late')->count();
            $abs[]  = Attendance::whereDate('created_at',$day)->where('status','absent')->count();
        }
        return [
            'datasets' => [
                ['label'=>'Op tijd','data'=>$on],
                ['label'=>'Te laat','data'=>$late],
                ['label'=>'Afwezig','data'=>$abs],
            ],
            'labels' => $labels,
        ];
    }
}

