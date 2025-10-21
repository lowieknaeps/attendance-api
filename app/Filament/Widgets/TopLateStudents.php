<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TopLateStudents extends BaseWidget
{
    protected static ?string $heading = 'Top laatkomers (laatste 30 dagen)';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        return Attendance::query()
            ->select('external_id', 'name', DB::raw('COUNT(*) AS late_count'))
            ->where('status', 'late')
            ->where('arrived', '>=', Carbon::now()->subDays(30))
            ->groupBy('external_id', 'name')
            ->orderByDesc('late_count');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Student')->searchable(),
            TextColumn::make('external_id')->label('ID')->sortable(),
            TextColumn::make('late_count')->label('Aantal keer te laat')->sortable(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getDefaultTableRecordsPerPage(): int
    {
        return 10;
    }

    /*
     * The table uses an aggregated query (groupBy) and therefore rows do not have the Eloquent primary key.
     * Filament expects a non-null string key for each record; return the external_id as the record key.
     */
    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) ($record->external_id ?? $record->id ?? '');
    }
}
