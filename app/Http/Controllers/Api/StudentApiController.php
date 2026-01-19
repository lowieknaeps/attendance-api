<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;

class StudentApiController extends Controller
{
    public function index()
    {
        $students = Student::select('external_id', 'name', 'card_uid', 'group')->get();

        return response()->json([
            'ok'      => true,
            'count'   => $students->count(),
            'students'=> $students,
        ]);
    }
}
