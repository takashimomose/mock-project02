<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\User;
use Illuminate\Http\Request;
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
// 公開ルート
Route::group([], function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [AuthenticationController::class, 'show'])->name('authentication.show');
    Route::post('/login', [AuthenticationController::class, 'store'])->name('authentication.store');
    Route::post('/logout', [AuthenticationController::class, 'destroy'])->name('authentication.destroy');

    // メール認証
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // ユーザーがメール認証を行うためのルート
    Route::get('/email/verify/{id}/{hash}', function (Request $request) {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->email))) {
            throw new \Illuminate\Validation\ValidationException('Invalid email verification link.');
        }

        $user->markEmailAsVerified();

        return redirect()->route('authentication.show')->with('verified', true);
    })->middleware(['signed'])->name('verification.verify');
});

Route::middleware(['check.role:user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');
});

Route::prefix('admin')->middleware('check.role:admin')->group(function () {
    Route::get('/login', [AuthenticationController::class, 'showAdmin'])->name('admin.auth.show');
    Route::post('/login', [AuthenticationController::class, 'storeAdmin'])->name('admin.auth.store');
    Route::post('/logout', [AuthenticationController::class, 'destroyAdmin'])->name('admin.auth.destroy');
});

Route::prefix('admin')->middleware(['auth', 'check.role:admin'])->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/attendance/staff/{id}', [StaffController::class, 'detail'])->name('admin.staff.detail');
    Route::get('/attendance/staff/{id}/export', [StaffController::class, 'exportCsv'])->name('admin.staff.export');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/{attendance_id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/correct/general', [AttendanceCorrectionController::class, 'correctGeneral'])->name('attendance.correct');
    Route::post('/attendance/correct/admin', [AttendanceCorrectionController::class, 'correctAdmin'])->name('admin.attendance.correct');
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'correct_index'])->name('attendance.correct_index');
});

Route::middleware(['auth', 'check.role:admin'])->group(function () {
    Route::get('/stamp_correction_request/approve/{id}', [AttendanceCorrectionController::class, 'show'])->name('correction.show');
    Route::post('/stamp_correction_request/approve', [AttendanceCorrectionController::class, 'approve'])->name('correction.approve');
});
