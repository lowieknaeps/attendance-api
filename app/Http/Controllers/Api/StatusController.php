<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStatusRequest;
use App\Http\Requests\StoreStatusBatchRequest;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function store(StoreStatusRequest $req): JsonResponse
    {
        $data = $req->validated();

        $row = Status::updateOrCreate(
            ['student_id'=>$data['student_id'], 'course_id'=>$data['course_id'], 'occurred_at'=>$data['occurred_at']],
            ['present'=>$data['present']]
        );

        return response()->json(['status'=>'ok','id'=>$row->id], 201);
    }

    public function storeBatch(StoreStatusBatchRequest $req): JsonResponse
    {
        $items = $req->validated()['items'];
        DB::transaction(function () use ($items) {
            foreach ($items as $d) {
                Status::updateOrCreate(
                    ['student_id'=>$d['student_id'], 'course_id'=>$d['course_id'], 'occurred_at'=>$d['occurred_at']],
                    ['present'=>$d['present']]
                );
            }
        });
        return response()->json(['status'=>'ok','count'=>count($items)], 201);
    }
}

