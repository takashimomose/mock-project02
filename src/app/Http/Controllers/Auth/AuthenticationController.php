<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function show()
    {
        if (auth()->check()) {
            return redirect('/attendance'); // ログインしている場合は/attendanceにリダイレクト
        }

        return view('auth.login');
    }

    public function store(AuthenticationRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role_id == User::ROLE_GENERAL) {

                if (is_null(Auth::user()->email_verified_at)) {
                    event(new Registered(Auth::user()));

                    Auth::logout();
                    return redirect()->route('verification.notice');
                }

                return redirect()->intended('/attendance');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => '管理者はログインできません',
            ]);
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }


    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showAdmin()
    {
        if (auth()->check()) {
            return redirect('/admin/attendance/list'); // ログインしている場合は/admin/attendance/listにリダイレクト
        }

        return view('auth.admin-login');
    }

    public function storeAdmin(AuthenticationRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role_id != User::ROLE_ADMIN) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => '一般ユーザーはログインできません',
                ]);
            }

            return redirect()->intended('/admin/attendance/list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    public function destroyAdmin(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
