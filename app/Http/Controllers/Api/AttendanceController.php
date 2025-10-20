<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
    $data = $request->validate([
        'items' => ['required','array','min:1'],
        'items.*.ID'          => ['nullable','integer'],     // uit Python
        'items.*.external_id' => ['nullable','integer'],     // of al gemapt
        'items.*.name'        => ['required','string','max:255'],
        'items.*.arrived'     => ['nullable','date'],
        'items.*.status'      => ['required','string','max:50'],
    ]);

        $rows = collect($data['items'])->map(function ($r) {
        $r['external_id'] = $r['external_id'] ?? $r['ID'] ?? null;
        unset($r['ID']);
        return $r;
    })->all();

        foreach ($rows as $r) {
        Attendance::create($r);
    }

        return response()->json(['ok' => true, 'count' => count($rows)], 201);
    }

}

