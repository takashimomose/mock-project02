<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (Auth::check()) {
            if ($role == 'admin' && Auth::user()->role_id != User::ROLE_ADMIN) {
                abort(403, '閲覧権限がありません');
            } elseif ($role == 'user' && Auth::user()->role_id == User::ROLE_ADMIN) {
                abort(403, '閲覧権限がありません');
            }
        }

        return $next($request);
    }
}
