<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\AttendanceSession;   
use App\Models\Attendance;
use Filament\Tables\Contracts\HasTable; 
use Filament\Tables\Concerns\InteractsWithTable;    
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;  


class StartAttendanceSession extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';    
    protected static string $view = 'filament.pages.start-attendance-session';
    protected static ?string $title = 'Aanwezigheid sessie';
    public bool $teacherPairedNotified = false;
    public array $data = [];
    public ?AttendanceSession $session = null;
    public ?string $scannerStatus = null;

    protected function showScannerNotification(): void
    {
        if (! $this->session) {
            return;
        }

        if (! $this->session->scanner_status) {
            return;
        }

        $notification = Notification::make()
            ->title($this->session->scanner_message);

        match ($this->session->scanner_status) {
            'paired'   => $notification->success(),
            'conflict' => $notification->danger(),
            'error'    => $notification->warning(),
            default    => $notification->info(),
        };

        $notification->send();

        $this->session->update([
            'scanner_status' => null,
            'scanner_message' => null,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Attendance::query()
                ->when($this->session, function ($q) {
                    $this->session = $this->session->fresh();
                    $this->checkTeacherPaired();
                    $this->session = $this->session?->fresh();
                    $this->updateScannerStatus();
                    $q->where('attendance_session_id', $this->session->id);
                })
                ->when(! $this->session, fn ($q) => $q->whereRaw('1=0'))
            )
            ->poll(fn () => $this->showScannerNotification(), 2000)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Student')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('arrived')
                    ->label('Aangekomen')
                    ->dateTime('H:i:s')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\IconColumn::make('note')
                    ->label('📝')
                    ->boolean(fn ($state) => !empty($state))
                    ->trueIcon('heroicon-o-pencil'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'present' => 'Aanwezig',
                        'late' => 'Laat',
                        'absent' => 'Afwezig',
                        default => $state,
                    })
                    
                    ->colors([
                        'success' => 'present',
                        'warning' => 'late',
                        'danger'  => 'absent',
                    ]),
            ])
            ->emptyStateHeading('Geen aanwezigheden')
            ->emptyStateDescription('Wacht tot studenten zich aanmelden via de scanner.')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Aanwezig',
                        'late'    => 'Laat',
                        'absent'  => 'Afwezig',
                    ]),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('arrived', 'desc');
    }

    protected function checkTeacherPaired(): void
    {
        if (! $this->session) {
            return;
        }

        if ($this->session->device_ip && ! $this->teacherPairedNotified) {
            Notification::make()
                ->title('Docent gescand')
                ->body('De scanner is succesvol gekoppeld.')
                ->success()
                ->send();

            $this->teacherPairedNotified = true;
        }
    }
    public function getScannerStatusProperty(): ?string
    {
        if (! $this->session) {
            return null;
        }

        return $this->session->device_ip ? 'paired' : 'not_paired';
    }
    protected function updateScannerStatus(): void
    {
        if (! $this->session) {
            $this->scannerStatus = null;
            return;
        }

        if ($this->session->device_ip) {
            $this->scannerStatus = 'paired';
        } else {
            $this->scannerStatus = 'not_paired';
        }
    }
    public function mount(): void
    {
        
        $this->session = AttendanceSession::query()
        ->with(['course', 'attendances'])
        ->where('teacher_id', auth()->id())
        ->whereNull('ended_at')
        ->latest('started_at')
        ->first();

        $this->form->fill([
            'course_external_id' => $this->session?->course_external_id,
        ]);
    }

    public function updatedSession()
    {
        if (! $this->session) return;

        if ($this->session->scanner_status === 'paired') {
            Notification::make()
                ->title('Scanner gekoppeld')
                ->success()
                ->send();
        }

        if ($this->session->scanner_status === 'conflict') {
            Notification::make()
                ->title('Scanner conflict')
                ->body($this->session->scanner_message)
                ->danger()
                ->send();
        }
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_external_id')
                    ->label('Kies vak')
                    ->placeholder('Selecteer een vak')
                    ->options(function () {
                        $excludedIds = auth()->user()
                            ->courses()
                            ->pluck('courses.id')
                            ->toArray();

                        return \App\Models\Course::query()
                            ->whereNotIn('id', $excludedIds)
                            ->orderBy('name')
                            ->pluck('name', 'external_id')
                            ->toArray();
                    })
                    ->required(),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('start')
                        ->label('Start sessie')
                        ->color('primary')
                        ->icon('heroicon-o-play')
                        ->visible(fn () => ! $this->session)
                        ->action(fn () => $this->start()),
                ])->alignLeft(),
            ])
            ->statePath('data');
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('stop')
                ->label('Stop')
                ->color('danger')
                ->visible(fn () => (bool) $this->session)
                ->requiresConfirmation()
                ->modalHeading('Sessie stoppen')
                ->modalDescription('Wil je de sessie stoppen? Je kan de CSV downloaden.')
                ->form([
                    Toggle::make('download_csv')
                        ->label('CSV downloaden na stoppen')
                        ->default(false),
                ])
                ->form([
                    Forms\Components\Textarea::make('note')
                        ->label('Opmerking / notitie')
                        ->placeholder('Bijv. Les vervroegd beëindigd wegens …')
                        ->rows(3),

                    Toggle::make('download_csv')
                        ->label('CSV downloaden na stoppen')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    $sessionId = $this->session->id;

                    $this->session->update(['ended_at' => now(),
                                            'note' => $data['note'] ?? null,]);
                    $this->session = null;
                    $this->form->fill(['course_external_id' => null]);

                    if (! empty($data['download_csv'])) {
                        $this->redirect(route('attendance.session.csv', $sessionId));
                    }
                }),
        ];
    }
    public function start(): void
    {
        AttendanceSession::where('teacher_id', auth()->id())
        ->whereNull('ended_at')
        ->update(['ended_at' => now()]);
        
        $courseExternalId = $this->form->getState()['course_external_id'];


        $this->session = AttendanceSession::create([
            'teacher_id' => auth()->id(),
            'course_external_id' => $courseExternalId,
            'started_at' => now(),
        ]);

        $course = \App\Models\Course::where('external_id', $courseExternalId)->firstOrFail();

        $students = $course->students()->get();

        foreach ($students as $student) {
            Attendance::create([
                'attendance_session_id' => $this->session->id,
                'course_id' => $course->id,
                'external_id' => $student->external_id,
                'name' => $student->name,
                'group' => $student->group,
                'status' => 'absent',
                'arrived' => null,
                'course_name' => $course->name,
            ]);
        }
    }

    public function getAttendancesProperty()
    {
        if (! $this->session) return collect();

        return Attendance::query()
            ->where('attendance_session_id', $this->session->id)
            ->orderByRaw("FIELD(status, 'present','late','absent')")
            ->orderBy('arrived')
            ->orderByDesc('arrived')
            ->get();
    }
    public function getSessionProperty()
    {
        return $this->session
            ? $this->session->fresh(['attendances'])
            : null;
    }

    public function getCountsProperty(): array
    {
        if (! $this->session) return ['present' => 0, 'late' => 0, 'absent' => 0];

        return Attendance::query()
            ->where('attendance_session_id', $this->session->id)
            ->selectRaw("
                SUM(status = 'present') as present,
                SUM(status = 'late') as late,
                SUM(status = 'absent') as absent
            ")
            ->first()
            ->toArray();
    }
    public function downloadCsv(int $sessionId): StreamedResponse
    {
        $session = AttendanceSession::with('course')
        ->where('id', $sessionId)
        ->where('teacher_id', auth()->id())
        ->firstOrFail();

        $filename = '' 
            . now()->format('Y-m-d_H-i-s') 
            . '_' 
            . ($session->course?->name ?? $session->course_external_id) 
            . '.csv';
            
        return response()->streamDownload(function () use ($sessionId) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['student', 'status', 'arrived','session_note']);

            Attendance::query()
                ->where('attendance_session_id', $sessionId)
                ->orderBy('name')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $a) {
                        fputcsv($out, [
                            $a->name,
                            $a->status,
                            optional($a->arrived)->format('Y-m-d H:i:s'),
                            $session->note,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
    public function getSessionElapsedProperty(): ?string
    {
        if (! $this->session || ! $this->session->started_at) {
            return null;
        }

        $diffInSeconds = now()->diffInSeconds($this->session->started_at);
        $minutes = floor($diffInSeconds / 60);
        $hours = floor($minutes / 60);

        if ($minutes < 1) {
            return '🟢 Net gestart';
        }

        if ($hours >= 1) {
            return 'Actief sinds ' . $hours . 'u ' . ($minutes % 60) . 'm';
        }

        return 'Actief sinds ' . $minutes . ' min';
    }
    public function checkScannerStatus(): void
    {
        if (! $this->session?->scanner_status) {
            return;
        }

        Notification::make()
            ->title($this->session->scanner_status)
            ->color(
                str_contains($this->session->scanner_status, 'niet')
                    ? 'danger'
                    : 'success'
            )
            ->send();

            $this->session->update(['scanner_status' => null]);
    }

    protected function getListeners(): array
    {
        return ['refreshScannerStatus' => '$refresh'];
    }

}