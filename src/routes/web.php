<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login', [AuthenticationController::class, 'show'])->name('authentication.show');
Route::post('/login', [AuthenticationController::class, 'store'])->name('authentication.store');
Route::post('/logout', [AuthenticationController::class, 'destroy'])->name('authentication.destroy');

Route::middleware(['check.role:user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/correct', [AttendanceCorrectionController::class, 'correct'])->name('attendance.correct');
});

Route::prefix('admin')->middleware('check.role:admin')->group(function () {
    Route::get('/login', [AuthenticationController::class, 'showAdmin'])->name('admin.auth.show');
    Route::post('/login', [AuthenticationController::class, 'storeAdmin'])->name('admin.auth.store');
    Route::post('/logout', [AuthenticationController::class, 'destroyAdmin'])->name('admin.auth.destroy');
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/attendance/staff/{id}', [StaffController::class, 'detail'])->name('admin.staff.detail');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/{attendance_id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'correct_index'])->name('attendance.correct_index');
});
