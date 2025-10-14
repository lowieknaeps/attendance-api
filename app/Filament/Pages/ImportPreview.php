<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Http;

class ImportPreview extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-down';
    protected static string $view = 'filament.pages.import-preview';

    public array $formData = ['course_id' => null, 'date' => null];
    public array $rows = [];

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('course_id')->numeric()->required()->label('Course'),
            Forms\Components\DatePicker::make('date')->label('Datum'),
            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('fetch')
                    ->label('Haal op')
                    ->action('fetch'),
            ]),
        ];
    }

    public function fetch(): void
    {
        $query = array_filter([
            'course_id' => $this->formData['course_id'] ?? null,
            'date'      => $this->formData['date'] ?? null,
        ]);

        $resp = Http::acceptJson()->get(route('api.import'), $query);
        $this->rows = $resp->successful() ? $resp->json() : [];
        if (!$resp->successful()) {
            $this->dispatch('notify', type: 'danger', message: 'Kon API niet ophalen');
        }
    }
}

