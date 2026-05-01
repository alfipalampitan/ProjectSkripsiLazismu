<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
{
    // 1. Cek apakah user sudah login
    if (!$request->user()) {
        return redirect()->route('login');
    }

    // 2. Cek apakah role user sesuai dengan parameter di route
    if ($request->user()->role !== $role) {
        
        // Redirect ke dashboard masing-masing jika akses ditolak
        if ($request->user()->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('error', 'Akses dilarang!');
        } 
        
        if ($request->user()->role === 'staff') {
            return redirect()->route('staff.dashboard')->with('error', 'Akses dilarang!');
        }

        // Default jika role tidak dikenal
        return redirect('/')->with('error', 'Anda tidak memiliki hak akses.');
    }

    return $next($request);
}
}
