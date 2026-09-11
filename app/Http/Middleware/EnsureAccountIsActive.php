<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang dinonaktifkan di halaman Karyawan keluar pada request berikutnya —
 * tidak menunggu sesinya habis sendiri. Masuk ulang ditolak oleh LoginForm.
 *
 * Tanpa ini "Nonaktifkan akun" hanya berlaku untuk login berikutnya: pelayan
 * yang diberhentikan tetap bisa memakai tablet yang masih masuk.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_active === false) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', 'Akun Anda sedang dinonaktifkan. Hubungi admin restoran untuk mengaktifkannya lagi.');
        }

        return $next($request);
    }
}
