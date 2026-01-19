<?php

namespace App\Filament\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class ScanLogs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string $view = 'filament.pages.scan-logs';
    protected static ?string $navigationLabel = 'Scan logs';
    protected static ?string $title = 'Scan logs';
    protected static ?int $navigationSort = 50;

    public string $logText = ' ';
    public int $lines = 200;
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->id, [1,3]);
    }
    public function mount(): void
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->id, [1,3])) {
            abort(403);
        }

        $this->loadLog();
    }

    public function loadLog(): void
    {
        $path = storage_path('logs/scans.log');

        if (! file_exists($path)) {
            $this->logText = "Geen scans.log file.\nPost eerst een scan.";
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $lines = array_values(array_filter($lines, fn($l) => trim($l) !== ''));
        $slice = array_slice($lines, -$this->lines);
        $slice = array_reverse($slice);

        $this->logText = implode("\n", $slice);
    }

    public function refreshLog(): void
    {
        $this->loadLog();
    }

    public function clearLog(): void
    {
        $path = storage_path('logs/scans.log');

        if (file_exists($path)) {
            file_put_contents($path, '');
        }

        $this->loadLog();

        Notification::make()
            ->title('Scan log cleared')
            ->success()
            ->send();
    }
}
