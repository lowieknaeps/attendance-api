<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Str;

class BootstrapSyncController extends Controller
{
    public function sync(Request $request)
    {
        $payload = $request->validate([
        'teacher_id'       => ['required', 'integer'],
        'teacher_name'     => ['nullable', 'string', 'max:255'],
        'courses'          => ['required', 'array', 'min:1'],
        'courses.*.id'     => ['nullable', 'string', 'max:255'],
        'courses.*.external_id' => ['nullable', 'string', 'max:255'],
        'courses.*.code'   => ['nullable', 'string', 'max:255'],
        'courses.*.name'   => ['required', 'string', 'max:255'],
    ]);
        $teacherId  = $payload['teacher_id'];   
        $teacherName = $payload['teacher_name'] ?? null;

        foreach ($payload['courses'] as $c) {
            $externalId = $c['external_id'] ?? $c['id'] ?? $c['code'] ?? null;

            if (!$externalId) {
                $externalId = Str::slug($c['name']);
            }
            Course::updateOrCreate(
                ['external_id' => $externalId, 'teacher_id' => $teacherId],
                [
                    'code'         => $c['code'] ?? null,
                    'name'         => $c['name'],
                    'teacher_name' => $teacherName,
                ]
            );
        }
    return response()->json(['ok' => true]);
    }
}
