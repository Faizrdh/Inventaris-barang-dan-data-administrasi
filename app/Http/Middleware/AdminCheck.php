<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user login
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Anda harus login!');
        }
        
        $user = Auth::user();
        
        // Cek role_id secara langsung, tanpa menggunakan method isAdmin()
        if ($user->role_id !== 1) {
            return redirect('/')->with('error', 'Akses tidak diizinkan!');
        }
        
        return $next($request);
    }
}