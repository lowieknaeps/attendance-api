<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StudentSyncService;    

class SyncStudents extends Command
{
    protected $signature = 'sync:students';
    protected $description = 'Sync students from Microsoft Graph';
    public function handle()
    {
        app(StudentSyncService::class)->syncAllStudents();
        $this->info('Students synced successfully');
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
}
