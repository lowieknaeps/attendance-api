<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\AttendanceController;





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
// token-auth wil:
// Route::middleware('auth:sanctum')->post('/status', [StatusController::class,'store']);
// Route::middleware('auth:sanctum')->post('/status/batch', [StatusController::class,'storeBatch']);
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/health', fn () => response()->json(['ok' => true, 'time' => now()->toIso8601String()]));

Route::post('/attendance', [AttendanceController::class, 'store']);             // batch POST 
Route::get('/attendance/today', [AttendanceController::class, 'today']);        // alleen vandaag
Route::get('/attendance/recent', [AttendanceController::class, 'recent']);      // laatste N records
Route::get('/attendance/by-date', [AttendanceController::class, 'byDate']);     // ?date=YYYY-MM-DD
Route::get('/attendance/student/{external_id}', [AttendanceController::class, 'byStudent']); // per student
Route::get('/attendance/top-late-students', [AttendanceController::class, 'topLateStudents']);
Route::get('/attendance/analytics', [AttendanceController::class, 'analytics']);
Route::post('/sync/bootstrap', [\App\Http\Controllers\Api\BootstrapSyncController::class, 'sync']);


