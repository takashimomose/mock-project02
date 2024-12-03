<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class AuthenticationController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(AuthenticationRequest $request)
    {
        $credentials = $request->validated();


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();


            return redirect()->intended('/attendance');
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
}
