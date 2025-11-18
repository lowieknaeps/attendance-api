<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Models\Attendance;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array {
    return [
        Action::make('exportCsv')
            ->label('Export CSV')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function () {
                return response()->streamDownload(function () {
                    $out = fopen('php://output','w');
                    fputcsv($out, ['id','external_id','name','status','arrived','created_at']);
                    Attendance::orderByDesc('created_at')->chunk(1000, function($rows) use($out){
                        foreach ($rows as $r) {
                            fputcsv($out, [$r->id,$r->external_id,$r->name,$r->status,$r->arrived,$r->created_at]);
                        }
                    });
                    fclose($out);
                }, 'attendances.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
            }),
    ];
}


}
