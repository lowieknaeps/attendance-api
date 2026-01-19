<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;  
use App\Filament\Pages\StartAttendanceSession;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return auth()->check() ? redirect('/admin') : redirect('/admin');
}); 

Route::get('/auth/redirect', [OAuthController::class, 'redirectToProvider'])
    ->name('auth.redirect');

Route::get('/auth/callback', [OAuthController::class, 'handleProviderCallback'])
    ->name('auth.callback');

Route::get('/attendance-sessions/{session}/csv', [StartAttendanceSession::class, 'downloadCsv'])
    ->name('attendance.session.csv')
    ->middleware(['auth']);