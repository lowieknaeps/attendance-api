<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;            // <-- belangrijk: Model importeren
use Illuminate\Http\Request;

class ImportController extends Controller
{
    // GET /api/import?course_id=2&date=2025-10-14 (beiden optioneel)
    public function index(Request $request)
    {
        // valideer query parameters (optioneel maar netjes)
        $validated = $request->validate([
            'course_id' => ['nullable','integer'],
            'date'      => ['nullable','date'], // yyyy-mm-dd
        ]);

        $q = Status::query()->select('id','student_id','course_id','present','occurred_at','created_at');

        if ($request->filled('course_id')) {
            $q->where('course_id', (int) $request->query('course_id'));
        }

        if ($request->filled('date')) {
            $q->whereDate('occurred_at', $request->query('date'));
        }

        return response()->json(
            $q->orderByDesc('occurred_at')->limit(500)->get()
        );
    }

    // POST /api/import/statuses (bestond al)
    public function statuses(Request $request)
    {
        $data = $request->validate([
            'statuses'                   => ['required','array','min:1'],
            'statuses.*.student_id'      => ['required','integer'],
            'statuses.*.course_id'       => ['required','integer'],
            'statuses.*.present'         => ['required','boolean'],
            'statuses.*.occurred_at'     => ['required','date'],
        ]);

        $inserted = 0; $dupes = 0;

        foreach ($data['statuses'] as $row) {
            $created = Status::firstOrCreate(
                [
                    'student_id'  => $row['student_id'],
                    'course_id'   => $row['course_id'],
                    'occurred_at' => $row['occurred_at'],
                ],
                ['present' => $row['present']]
            );

            $created->wasRecentlyCreated ? $inserted++ : $dupes++;
        }

        return response()->json(['status' => 'ok', 'inserted' => $inserted, 'duplicates' => $dupes]);
    }
}
