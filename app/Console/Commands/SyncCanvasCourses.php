<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\CanvasService;
use Illuminate\Console\Command;

class SyncCanvasCourses extends Command
{
    protected $signature = 'canvas:sync-courses {--teacher_id=}';
    protected $description = 'Sync Canvas courses into local DB';

    public function handle(CanvasService $canvas)
    {
        $token = config('services.canvas.token');
        $baseUrl = config('services.canvas.base_url');

        if (! $token || ! $baseUrl) {
            $this->error('Missing CANVAS_BASE_URL or CANVAS_TOKEN in .env');
            return self::FAILURE;
        }

        $teacherId = (int) ($this->option('teacher_id') ?? 1);
        if ($teacherId <= 0) $teacherId = 1;

        $courses = collect($canvas->courses($token, $baseUrl));

        $now = now();

        $rows = collect($courses)
                ->filter(fn ($c) =>
                !empty($c['id']) &&
                !empty($c['course_code']) &&
                !empty($c['code']) 
            )
            ->map(function ($c) use ($teacherId) {
                return [
                    'external_id' => (string) $c['id'],
                    'teacher_id'  => $teacherId,
                    'name'        => $c['name'] ?? 'Untitled',
                    'code'        => $c['course_code'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            })
            ->values()
            ->all();

        Course::upsert(
            $rows,
            ['external_id', 'teacher_id'],
            ['name', 'code', 'updated_at']
        );
        $this->info('Synced ' . count($rows) . ' courses.');
        return self::SUCCESS;
    }
}
