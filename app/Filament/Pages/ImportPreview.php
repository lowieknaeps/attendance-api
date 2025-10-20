<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ImportPreview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string $view = 'filament.pages.import-preview'; // 
    protected function getFormSchema(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
            return [
                Action::make('push')
                    ->label('Importeer naar API')
                    ->color('success')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->action(function () {
                        $payload = [
                            [
                                'student_id'  => 7,
                                'course_id'   => 2,
                                'present'     => true,
                                'occurred_at' => now()->toIso8601String(),
                            ],
                        ];

                        // ⬇︎ stuur 'items' i.p.v. 'rows'
                        $resp = Http::acceptJson()
                            ->asJson() // expliciet JSON (mag, is duidelijk)
                            ->post(route('api.import.statuses'), ['items' => $payload]);

                        if ($resp->successful() && data_get($resp->json(), 'ok') === true) {
                            Notification::make()
                                ->title('Import gelukt')
                                ->body('API antwoordde OK ('.($resp->json('count') ?? 0).' rijen).')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import mislukt')
                                ->body('Status: '.$resp->status().' — '.$resp->body())
                                ->danger()
                                ->send();
                        }
                    }),
            ];
    }

}
