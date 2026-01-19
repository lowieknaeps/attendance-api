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
    protected static ?string $title = 'Verborgen vakken';
    protected static ?string $navigationLabel = 'Vakken verbergen';
    protected static string $view = 'filament.pages.choose-courses';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $excluded = $user?->courses()->pluck('courses.id')->all() ?? [];

        $this->form->fill([
            'excluded_course_ids' => $excluded,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                MultiSelect::make('excluded_course_ids')
                    ->label('Verberg deze vakken')
                    ->helperText('Deze vakken zullen niet zichtbaar zijn bij het starten van een sessie')
                    ->options(
                        Course::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),
            ])
            ->statePath('data');
    }
    public function save(): void
    {
        $state = $this->form->getState();

        $ids = $state['excluded_course_ids'] ?? [];

        auth()->user()->courses()->sync($ids);

        Notification::make()
            ->title('Verborgen vakken opgeslagen')
            ->success()
            ->send();

        $this->redirect('/admin');
    }

}
