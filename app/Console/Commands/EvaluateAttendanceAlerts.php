<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Support\AttendanceAlerts;

class EvaluateAttendanceAlerts extends Command
{
    protected $signature = 'attendance:alerts';
    protected $description = 'Controleer voor aanwezigheden en toon notificaties';

    public function handle(): int
    {
        $events = AttendanceAlerts::evaluateDaily();

        $this->info('Attendance alerts uitgevoerd.');
        $this->info('Aantal meldingen: ' . count($events));

        if (!empty($events)) {
            foreach ($events as $e) {
                $this->line('- ' . $e['message']);
            }
        }

        return self::SUCCESS;
    }
}

