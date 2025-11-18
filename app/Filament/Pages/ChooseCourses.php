<?php

namespace App\Filament\Pages;

use App\Models\Course;
use Filament\Actions\Action;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ChooseCourses extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $title = 'Koppel je vakken';
    protected static string $view = 'filament.pages.choose-courses';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $selected = $user?->courses()->pluck('courses.id')->all() ?? [];

        $this->form->fill([
            'course_ids' => $selected,
        ]);
    }
    //Kiezen van vakken
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                MultiSelect::make('course_ids')
                    ->label('Kies je vakken')
                    ->options(Course::query()->orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('opslaan')
                ->label('Opslaan')
                ->submit('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $ids = $state['course_ids'] ?? [];

        auth()->user()->courses()->sync($ids);

        Notification::make()
            ->title('Vakken gekoppeld')
            ->success()
            ->send();

        $this->redirect('/admin');
    }
}
