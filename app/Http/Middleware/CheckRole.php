<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Array of allowed roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        // Ambil role user yang sedang login
        $userRole = Auth::user()->role;

        // Cek apakah role user ada di dalam daftar role yang diizinkan
        if (in_array($userRole, $roles)) {
            // Jika diizinkan, lanjutkan request
            return $next($request);
        }

        // Jika tidak diizinkan, kembalikan halaman 403 Forbidden
        abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
    }
}