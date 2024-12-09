<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AttendanceController;
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

Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

Route::get('/login', [AuthenticationController::class, 'show'])->name('authentication.show');
Route::post('/login', [AuthenticationController::class, 'store'])->name('authentication.store');
Route::post('/logout', [AuthenticationController::class, 'destroy'])->name('authentication.destroy');