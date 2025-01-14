<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function show()
    {
        if (auth()->check()) {
            return redirect('/attendance'); // ログインしている場合は/attendanceにリダイレクト
        }
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Registeredイベントを発火して認証メールを送信
        event(new Registered($user));

        return redirect()->route('verification.notice'); // 認証待ちページにリダイレクト
    }
}
