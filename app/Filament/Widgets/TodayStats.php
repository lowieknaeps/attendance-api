<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class TodayStats extends BaseWidget
{

    protected ?string $heading = 'Vandaag – overzicht';

    protected function getCards(): array
    {
        $today = Carbon::today();

        $present = Attendance::where('status','present')->whereDate('arrived', $today)->count();
        $late    = Attendance::where('status','late')->whereDate('arrived', $today)->count();
        $absent  = Attendance::where('status','absent')->whereDate('arrived', $today)->count();
        $unique  = Attendance::whereDate('arrived', $today)->distinct('external_id')->count('external_id');

        return [
            Card::make('Aanwezig', $present),
            Card::make('Te laat', $late),
            Card::make('Afwezig', $absent),
        ];
    }
}
