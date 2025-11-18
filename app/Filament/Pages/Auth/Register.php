<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms;
use App\Models\Attendance;

class Register extends BaseRegister
{
    protected static bool $shouldRegisterNavigation = false;

    protected function getFormSchema(): array
    {
        $courseOptions = Attendance::query()
            ->whereNotNull('course_id')
            ->select('course_id')
            ->selectRaw('MAX(course_name) as course_name')
            ->groupBy('course_id')
            ->orderBy('course_id')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->course_id => $r->course_id . ($r->course_name ? ' — ' . $r->course_name : ''),
            ])
            ->toArray();

        return [
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),

            Forms\Components\Select::make('courses')
                ->label('Vakken die je geeft')
                ->options($courseOptions)
                ->multiple()
                ->searchable()
                ->preload()
                ->columnSpanFull(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'teacher';
        $data['courses'] = $data['courses'] ?? []; 
        return $data;
    }
}
