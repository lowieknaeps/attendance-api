<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
class TopLateStudents extends BaseWidget
{
    protected static ?string $heading = 'Top laatkomers (laatste 30 dagen)';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        return Attendance::query()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->where('status', 'late')
            ->select('external_id', 'name', DB::raw('COUNT(*) AS late_count'))
            ->groupBy('external_id', 'name')
            ->orderByRaw('COUNT(*) DESC');  
    }
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Student')->searchable(),
            TextColumn::make('external_id')->label('ID')->sortable(),
            TextColumn::make('late_count')->label('Aantal keer te laat')->sortable(),
        ];
    }
    public function getTableRecordKey($record): string
    {
        $key = null;

        if (is_object($record)) {
            if (isset($record->external_id) && $record->external_id !== null) {
                $key = $record->external_id;
            } elseif (isset($record->id) && $record->id !== null) {
                $key = $record->id;
            } elseif (isset($record->name) && $record->name !== null) {
                $key = $record->name;
            } elseif (isset($record->late_count)) {
                $key = 'late-'.$record->late_count;
            }
        }
        return (string) ($key ?? 'record');
    }
}

