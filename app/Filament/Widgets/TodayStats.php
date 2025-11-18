<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TodayStats extends BaseWidget
{
    protected ?string $heading = 'Vandaag – overzicht';

    protected function getCards(): array
    {
        $today = Carbon::today(config('app.timezone'))->toDateString();

        $base = Attendance::query()
            ->whereDate(DB::raw("DATE(COALESCE(arrived, created_at))"), $today);

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
