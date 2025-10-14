<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Filament\Resources\StatusResource\RelationManagers;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('student_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('course_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('present')
                    ->required(),
                Forms\Components\DateTimePicker::make('occurred_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id')->label('Student')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('course_id')->label('Vak')->sortable()->searchable(),
                Tables\Columns\IconColumn::make('present')->boolean()->label('Aanwezig?'),
                Tables\Columns\TextColumn::make('occurred_at')->dateTime()->label('Datum'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Aangekomen'),
            ])
            ->filters([
                SelectFilter::make('present')
                    ->label('Aanwezigheid')
                    ->options([true => 'Aanwezig', false => 'Afwezig']),
                Filter::make('today')
                    ->label('Vandaag')
                    ->query(fn ($query) => $query->whereDate('occurred_at', today())),
            ])
            ->defaultSort('occurred_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}
