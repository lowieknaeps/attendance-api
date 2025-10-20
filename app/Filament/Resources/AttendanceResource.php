<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

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
                    'present' => 'Present',
                    'late'    => 'Te laat',
                    'absent'  => 'Afwezig',
                ])
                ->label('Status')
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
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
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    // widgets koppeling
    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\TodayStats::class,
            \App\Filament\Widgets\TodayStatusDonut::class,
            \App\Filament\Widgets\TopLateStudents::class,
        ];
    }
}
