<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next,$role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // اگر نقش درست است، اجازه عبور بده
        if ($user->role === $role) {
            return $next($request);
        }

        // اگر نقش اشتباه است، به داشبورد مربوط به خودش هدایت کن
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard'); // حتما ->route داشته باشد
        }

        if ($user->role === 'employee') {
            return redirect()->route('employee.dashboard'); // حتما ->route داشته باشد
        }

        return redirect('/');
    }
}
