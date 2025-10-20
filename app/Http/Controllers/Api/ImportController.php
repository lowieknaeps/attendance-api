<?php
// app/Http/Controllers/Api/ImportController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStatusesRequest; //of Request
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Status;


class ImportController extends Controller
{
    public function statuses(ImportStatusesRequest $request): JsonResponse
    {   
        $items = $request->validated()['items'];
        $count = 0;

        foreach ($items as $row) {
            Status::updateOrCreate(
                [
                    'student_id'  => $row['student_id'],
                    'course_id'   => $row['course_id'],
                    'occurred_at' => $row['occurred_at'],
                ],
                [
                    'present' => (bool) $row['present'],
                ]
            );
            $count++;
        }

        return response()->json([
            'ok'     => true,
            'count'  => $count,
            'status' => 'received',
        ], 200);
    }
}
