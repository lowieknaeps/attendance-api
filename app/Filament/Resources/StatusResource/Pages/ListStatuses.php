<?php

namespace App\Filament\Resources\StatusResource\Pages;

use App\Filament\Resources\StatusResource;
use App\Models\Status;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListStatuses extends ListRecords
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['id','student_id','course_id','present','occurred_at','created_at']);

                        Status::orderByDesc('occurred_at')
                            ->chunk(1000, function ($rows) use ($out) {
                                foreach ($rows as $r) {
                                    fputcsv($out, [
                                        $r->id,
                                        $r->student_id,
                                        $r->course_id,
                                        $r->present ? 1 : 0,
                                        $r->occurred_at,
                                        $r->created_at,
                                    ]);
                                }
                            });

                        fclose($out);
                    }, 'statuses.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
        ];
    }
}

