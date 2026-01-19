<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScanController extends Controller
{
    /**
     * ESP stuurt hex zoals "24874DA4"
     */
    private function espHexToDecimalUid(string $espHex): int
    {
        $espHex = strtoupper(preg_replace('/[^0-9A-F]/', '', $espHex));
        $espHex = str_pad($espHex, 8, '0', STR_PAD_LEFT);

        $bytes = array_reverse(str_split($espHex, 2));

        return hexdec(implode('', $bytes));
    }

    public function store(Request $request)
    {
        \Log::channel('scans')->info('SCAN RECEIVED', [
            'ip'   => $request->ip(),
            'body' => $request->all(),
        ]);

        $payload = $request->all();

        if (!isset($payload['items'])) {
            $payload = [
                'items' => [[
                    'card_uid'   => $payload['card_uid']   ?? null,
                    'external_id'=> $payload['external_id']?? null,
                    'status'     => $payload['status']     ?? null,
                    'arrived'    => $payload['arrived']    ?? null,
                    'scanned_at' => $payload['scanned_at'] ?? null,
                ]]
            ];
        }

        $data = validator($payload, [
            'items' => ['required','array','min:1'],
            'items.*.card_uid'    => ['nullable','string'],
            'items.*.external_id' => ['nullable','string'],
            'items.*.status'      => ['nullable','in:present,late,absent'],
            'items.*.arrived'     => ['nullable','date'],
            'items.*.scanned_at'  => ['nullable','date'],
        ])->validate();

        $results = [];
        $hasSpecial = false;
        $scannerIp = $request->ip();

        foreach ($data['items'] as $item) {
        try {
            $decimalUid = !empty($item['card_uid'])
                ? (string) $this->espHexToDecimalUid($item['card_uid'])
                : null;

            $scannerIp = $request->ip();

            $pairedSession = AttendanceSession::where('device_ip', $scannerIp)
                ->whereNull('ended_at')
                ->latest('started_at')
                ->first();

            $teacher = $decimalUid
                ? User::where('card_uid', $decimalUid)->first()
                : null;

            /*
            * DOCENTENKAART
            */
            if ($teacher) {

            $teacherSession = AttendanceSession::where('teacher_id', $teacher->id)
                ->whereNull('ended_at')
                ->latest('started_at')
                ->first();

            if (! $teacherSession) {
                $results[] = [
                    'ok' => false,
                    'type' => 'teacher',
                    'message' => 'Geen actieve sessie voor deze docent',
                ];
                $hasSpecial = true;
                continue;
            }

            $pairedSession = AttendanceSession::where('device_ip', $scannerIp)
                ->whereNull('ended_at')
                ->latest('started_at')
                ->first();

            if ($pairedSession && $pairedSession->teacher_id !== $teacher->id) {
                $results[] = [
                    'ok' => false,
                    'type' => 'conflict',
                    'message' => 'Scanner is al in gebruik door een andere sessie',
                ];
                $hasSpecial = true;
                continue;
            }

            if ($pairedSession && $pairedSession->teacher_id === $teacher->id) {
                $results[] = [
                    'ok' => true,
                    'type' => 'teacher',
                    'action' => 'already_paired',
                    'message' => 'Scanner is al gekoppeld aan jouw sessie',
                ];
                $hasSpecial = true;
                continue;
            }

            $teacherSession->update([
                'device_ip' => $scannerIp,
                'scanner_status' => 'paired',
                'scanner_message' => 'Scanner gekoppeld',
            ]);

            $results[] = [
                'ok' => true,
                'type' => 'teacher',
                'action' => 'paired',
                'message' => 'Scanner gekoppeld',
                'session_id' => $teacherSession->id,
                'course_external_id' => $teacherSession->course_external_id,
            ];

            $hasSpecial = true;
            continue;
        }

            /*
            * GEEN DOCENT → STUDENT
            */
            if (! $pairedSession) {
                $results[] = [
                    'ok' => false,
                    'type' => 'error',
                    'message' => 'Scanner niet gekoppeld. Scan eerst docent.',
                ];
                $hasSpecial = true;
                continue;
            }

            $session = $pairedSession;

            $arrived = isset($item['arrived'])
                ? Carbon::parse($item['arrived'])
                : (isset($item['scanned_at'])
                    ? Carbon::parse($item['scanned_at'])
                    : now());

            $status = $arrived->gt(
                Carbon::parse($session->started_at)->addMinutes(12)
            ) ? 'late' : 'present';

            $student = $decimalUid
                ? Student::where('card_uid', $decimalUid)->first()
                : null;

            if (! $student) {
                $results[] = [
                    'ok' => false,
                    'type' => 'student',
                    'message' => 'Onbekende studentenkaart',
                ];
                $hasSpecial = true;
                continue;
            }

            $attendance = Attendance::where('attendance_session_id', $session->id)
                ->where('external_id', $student->external_id)
                ->first();

            if ($attendance && in_array($attendance->status, ['present', 'late'])) {
                $results[] = [
                    'ok' => true,
                    'type' => 'student',
                    'action' => 'already_scanned',
                    'student' => ['name' => $student->name],
                    'status' => $attendance->status,
                ];
                $hasSpecial = true;
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'external_id' => $student->external_id,
                ],
                [
                    'name' => $student->name,
                    'group' => $student->group,
                    'status' => $status,
                    'arrived' => $arrived,
                    'source' => 'wifi-scanner',
                ]
            );

            $results[] = [
                'ok' => true,
                'type' => 'student',
                'action' => 'scanned',
                'student' => ['name' => $student->name],
                'status' => $status,
            ];

        } catch (\Throwable $e) {
            \Log::error('SCAN ERROR', [
                'ip' => $scannerIp,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $results[] = [
                'ok' => false,
                'type' => 'error',
                'message' => 'Internal scan error',
            ];

            $hasSpecial = true;
        }
    }

        return response()->json([
            'ok' => true,
            'results' => $results,
        ], $hasSpecial ? 202 : 200);
    }
}
