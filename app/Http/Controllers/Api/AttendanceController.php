<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Support\AttendanceAlerts;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Group;   

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'items'               => ['required','array','min:1'],

            'items.*.ID'          => ['nullable','integer'],
            'items.*.external_id' => ['nullable','integer'],
            'items.*.name'        => ['required','string','max:255'],
            'items.*.status'      => ['required','string','max:50'],
            'items.*.arrived'     => ['nullable','date'],

            'items.*.course_id'   => ['nullable'],                  
            'items.*.course_name' => ['nullable','string','max:255'],
            'items.*.group'       => ['nullable'],                  
            'items.*.room'        => ['nullable','string','max:255'],
            'items.*.device_id'   => ['nullable','string','max:255'],
            'items.*.location'    => ['nullable','string','max:255'],
            'items.*.source'      => ['nullable','string','max:255'],
            'items.*.notes'       => ['nullable','string'],
        ]);

        $rows = collect($data['items'])->map(function (array $r) {
            $externalId = $r['external_id'] ?? $r['ID'] ?? null;

            $groupName = $r['group'] ?? null;
            if (is_array($groupName)) {
                $groupName = implode(',', $groupName);
            }

            $source = $r['source'] ?? 'python-app';

            $courseFkId  = null;
            $courseName  = $r['course_name'] ?? null;
            $externalCourseId = $r['course_id'] ?? null;

            if (!empty($externalCourseId)) {
                $course = Course::where('external_id', (string) $externalCourseId)->first();

                if ($course) {
                    $courseFkId = $course->id;
                    $courseName = $courseName ?: $course->name;
                }
            }

            $groupFkId = null;
            if (!empty($groupName)) {
                $groupModel = Group::where('name', $groupName)->first();

                if ($groupModel) {
                    $groupFkId = $groupModel->id;
                }
            }

            return [
                'external_id'   => $externalId,
                'name'          => $r['name'],
                'status'        => $r['status'],
                'arrived'       => $r['arrived'] ?? null,

                'course_id'     => $externalCourseId,
                'course_name'   => $courseName,
                'course_fk_id'  => $courseFkId,

                'group'         => $groupName,
                'group_fk_id'   => $groupFkId,

                'room'          => $r['room'] ?? null,
                'device_id'     => $r['device_id'] ?? null,
                'location'      => $r['location'] ?? null,
                'source'        => $source,
                'notes'         => $r['notes'] ?? null,
            ];
        });

        $count = 0;

        foreach ($rows as $payload) {
            $attendance = Attendance::updateOrCreate(
                [
                    'external_id' => $payload['external_id'],
                    'arrived'     => $payload['arrived'],
                ],
                $payload
            );

            $count++;

            if (!empty($attendance->external_id)) {
                AttendanceAlerts::evaluateForStudent($attendance->external_id);
            }
        }

        AttendanceAlerts::evaluateDaily();

        return response()->json(['ok' => true, 'count' => $count], 201);
    }

    public function today()
    {
        $ts = DB::raw('COALESCE(arrived, created_at)');
        $rows = Attendance::query()
            ->whereDate($ts, now()->toDateString())
            ->orderByDesc($ts)
            ->get(['external_id','name','arrived','status','created_at','course_id','group','room']);

        return response()->json([
            'ok'    => true,
            'count' => $rows->count(),
            'items' => $rows,
        ]);
    }

    public function recent(Request $request)
    {
        $ts = DB::raw('COALESCE(arrived, created_at)');
        $limit = min(max((int) $request->integer('limit', 50), 1), 500);

        $rows = Attendance::query()
            ->orderByDesc($ts)
            ->limit($limit)
            ->get(['external_id','name','arrived','status','created_at','course_id','group','room']);

        return response()->json([
            'ok'    => true,
            'count' => $rows->count(),
            'items' => $rows,
        ]);
    }

    public function byDate(Request $request)
    {
        $request->validate(['date' => ['required','date']]);
        $date = Carbon::parse($request->input('date'))->toDateString();
        $ts = DB::raw('COALESCE(arrived, created_at)');

        $rows = Attendance::query()
            ->whereDate($ts, $date)
            ->orderBy($ts)
            ->get(['external_id','name','arrived','status','created_at','course_id','group','room']);

        return response()->json([
            'ok'    => true,
            'date'  => $date,
            'count' => $rows->count(),
            'items' => $rows,
        ]);
    }

    public function byStudent($external_id)
    {
        $ts = DB::raw('COALESCE(arrived, created_at)');

        $rows = Attendance::query()
            ->where('external_id', $external_id)
            ->orderByDesc($ts)
            ->limit(500)
            ->get(['external_id','name','arrived','status','created_at','course_id','group','room']);

        return response()->json([
            'ok'          => true,
            'external_id' => $external_id,
            'count'       => $rows->count(),
            'items'       => $rows,
        ]);
    }

    public function topLateStudents(Request $request)
    {
        $days  = (int) $request->query('days', 30);
        $limit = (int) $request->query('limit', 10);
        $ts    = DB::raw('COALESCE(arrived, created_at)');

        $data = Attendance::query()
            ->select('external_id', 'name', DB::raw('COUNT(*) AS late_count'))
            ->where('status', 'late')
            ->where($ts, '>=', Carbon::now()->subDays($days))
            ->groupBy('external_id', 'name')
            ->orderByDesc('late_count')
            ->limit($limit)
            ->get();

        return response()->json([
            'ok'                => true,
            'days'              => $days,
            'limit'             => $limit,
            'top_late_students' => $data,
            'generated_at'      => now()->toIso8601String(),
        ]);
    }

    public function analytics(Request $request)
    {
        $groupBy  = $request->query('group_by', 'day'); // day|week|month|year
        $from     = $request->query('from');
        $to       = $request->query('to');
        $courseId = $request->query('course_id');

        $start = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end   = $to   ? Carbon::parse($to)->endOfDay()   : Carbon::now()->endOfDay();

        $ts = 'COALESCE(arrived, created_at)';
        switch ($groupBy) {
            case 'year':  $groupExpr = "DATE_FORMAT($ts, '%Y')";     break;
            case 'month': $groupExpr = "DATE_FORMAT($ts, '%Y-%m')";  break;
            case 'week':  $groupExpr = "DATE_FORMAT($ts, '%x-W%v')"; break;
            case 'day':
            default:      $groupExpr = "DATE($ts)"; $groupBy = 'day'; break;
        }

        $rows = Attendance::query()
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->whereBetween(DB::raw($ts), [$start, $end])
            ->selectRaw("$groupExpr AS grp")
            ->selectRaw("COUNT(*) AS total")
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent")
            ->groupBy(DB::raw($groupExpr))
            ->orderBy(DB::raw($groupExpr))
            ->get();

        $data = $rows->map(function ($r) {
            $total = (int) $r->total;
            return [
                'label'       => $r->grp,
                'total'       => $total,
                'present'     => (int) $r->present,
                'late'        => (int) $r->late,
                'absent'      => (int) $r->absent,
                'present_pct' => $total ? round($r->present / $total * 100, 1) : 0.0,
                'late_pct'    => $total ? round($r->late    / $total * 100, 1) : 0.0,
                'absent_pct'  => $total ? round($r->absent  / $total * 100, 1) : 0.0,
            ];
        });

        $overallTotal   = $data->sum('total');
        $overallPresent = $data->sum('present');
        $overallLate    = $data->sum('late');
        $overallAbsent  = $data->sum('absent');

        return response()->json([
            'ok'          => true,
            'group_by'    => $groupBy,
            'from'        => $start->toDateString(),
            'to'          => $end->toDateString(),
            'course_id'   => $courseId,
            'overall'     => [
            'total'       => $overallTotal,
            'present'     => $overallPresent,
            'late'        => $overallLate,
            'absent'      => $overallAbsent,
            'present_pct' => $overallTotal ? round($overallPresent / $overallTotal * 100, 1) : 0.0,
            'late_pct'    => $overallTotal ? round($overallLate    / $overallTotal * 100, 1) : 0.0,
            'absent_pct'  => $overallTotal ? round($overallAbsent  / $overallTotal * 100, 1) : 0.0,
        ],
            'series'      => $data,
            'generated_at'=> now()->toIso8601String(),
        ]);
    }
    
}
