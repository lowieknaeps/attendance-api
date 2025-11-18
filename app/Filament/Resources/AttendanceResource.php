<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Aanwezigheden';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('external_id')->label('Student-ID')->required(),
            Forms\Components\TextInput::make('name')->label('Naam')->required(),
            Forms\Components\DateTimePicker::make('arrived')->label('Aangekomen'),
            Forms\Components\Select::make('status')
                ->options([
                    'present' => 'Aanwezig',
                    'late'    => 'Te laat',
                    'absent'  => 'Afwezig',
                ])
                ->label('Status')
                ->required(),
            Forms\Components\Textarea::make('notes')->label('Notities')->rows(6)->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->defaultSort('arrived', 'desc')
            ->columns([
            TextColumn::make('external_id')->label('Student-ID')->sortable()->searchable(),
            TextColumn::make('name')->label('Naam')->sortable()->searchable(),
            BadgeColumn::make('status')
                ->colors([
                    'success' => 'present',
                    'warning' => 'late',
                    'danger'  => 'absent',
                ])
                ->label('Status')
                ->sortable(),

            TextColumn::make('arrived')->label('Aangekomen')->dateTime('Y-m-d H:i')->sortable(),
            TextColumn::make('created_at')->label('Ingevoerd')->since(),
            TextColumn::make('course.name')
                ->label('Vak')
                ->formatStateUsing(fn ($state, \App\Models\Attendance $record) =>
                    $state ?? $record->course_id
                )
                ->sortable()
                ->searchable(),

            TextColumn::make('group')->label('Groep')->sortable()->searchable(),
            TextColumn::make('room')->label('Lokaal'),

            TextColumn::make('notes')
                ->label('Notities')
                ->limit(60)
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
            ->filters([
                SelectFilter::make('course_id')
                    ->label('Vak')
                    ->relationship('course', 'name')  
                    ->searchable()
                    ->preload()
                    ->indicator('Vak'),

                SelectFilter::make('group')
                    ->label('Groep')
                    ->multiple()
                    ->options(fn () => Attendance::query()
                        ->whereNotNull('group')
                        ->distinct()
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->indicator('Groep'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit'   => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\TodayStats::class,
            \App\Filament\Widgets\TodayStatusDonut::class,
            \App\Filament\Widgets\TopLateStudents::class,
            \App\Filament\Widgets\WeekAttendanceChart::class,
        ];
    }
    public function scopeToday(Builder $q): Builder
    {
        return $q->where(function (Builder $q) {
            $q->whereDate('arrived', Carbon::today())
              ->orWhere(function (Builder $q) {
                  $q->whereNull('arrived')->whereDate('created_at', Carbon::today());
              });
        });
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user && ($user->is_admin ?? false)) {
            return $query;
        }

        if (! $user) {
            return $query;
        }

        $courseExternalIds = $user->courses()->pluck('external_id')->all();

        if (empty($courseExternalIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('course_id', $courseExternalIds);
    }

    public function scopeSince(Builder $q, Carbon|string $from): Builder
    {
        $from = $from instanceof Carbon ? $from : Carbon::parse($from);

        return $q->where(function (Builder $q) use ($from) {
            $q->whereNotNull('arrived')->where('arrived', '>=', $from)
              ->orWhere(function (Builder $q) use ($from) {
                  $q->whereNull('arrived')->where('created_at', '>=', $from);
              });
        });
    }
}
