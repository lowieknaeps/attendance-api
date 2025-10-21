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

Route::post('/attendances', [AttendanceController::class, 'store']);
