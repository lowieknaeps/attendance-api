<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class AttendanceArchive extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $title = 'Aanwezigheid archief';
    protected static ?string $navigationLabel = 'Archief';
    protected static string $view = 'filament.pages.attendance-archive';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AttendanceSession::query()
                    ->where('teacher_id', auth()->id())
                    ->whereNotNull('ended_at')
                    ->with(['course', 'teacher'])
                    ->latest('started_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Vak')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Docent'),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Start')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Einde')
                    ->dateTime('H:i'),

                Tables\Columns\TextColumn::make('attendances_count')
                    ->label('Studenten')
                    ->counts('attendances'),
                    
                Tables\Columns\TextColumn::make('note')
                    ->label('Opmerking')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('download_csv')
                    ->label('CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (AttendanceSession $record) =>
                        redirect()->route('attendance.session.csv', $record->id)
                    ),
            ])
            ->emptyStateHeading('Geen oudere sessies')
            ->emptyStateDescription('Wacht tot je een sessie hebt afgerond.')
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Vak')
                    ->relationship('course', 'name'),

                Tables\Filters\Filter::make('datum')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Van'),
                        Forms\Components\DatePicker::make('to')->label('Tot'),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query
                            ->when(
                                $data['from'] ?? null,
                                fn ($q) => $q->whereDate('started_at', '>=', $data['from'])
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn ($q) => $q->whereDate('started_at', '<=', $data['to'])
                            )
                    ),

                Tables\Filters\Filter::make('student')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Student naam')
                            ->placeholder('bv. Janssens'),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query->when(
                            $data['name'] ?? null,
                            fn ($q) => $q->whereHas('attendances', fn ($sub) =>
                                $sub->where('name', 'like', '%' . $data['name'] . '%')
                            )
                        )
                    ),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearArchive')
                ->label('Archief wissen')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Archief wissen')
                ->modalDescription('Alle beëindigde sessies van jou worden definitief verwijderd.')
                ->modalSubmitActionLabel('Ja, wissen')
                ->visible(fn () =>
                    AttendanceSession::where('teacher_id', auth()->id())
                        ->whereNotNull('ended_at')
                        ->exists()
                )
                ->action(function () {
                    $sessions = AttendanceSession::where('teacher_id', auth()->id())
                        ->whereNotNull('ended_at')
                        ->get();

                    $sessionIds = $sessions->pluck('id');

                    Attendance::whereIn('attendance_session_id', $sessionIds)->delete();
                    AttendanceSession::whereIn('id', $sessionIds)->delete();

                    Notification::make()
                        ->title('Archief gewist')
                        ->body('Je archief is succesvol leeggemaakt.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
