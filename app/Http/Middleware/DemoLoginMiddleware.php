<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DemoLoginMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.login.enabled') || Auth::check()) {
            return $next($request);
        }

        $email = config('demo.login.user_email');

        if (! is_string($email) || $email === '') {
            return $next($request);
        }

        $user = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if (! $user) {
            Log::warning('Demo login user was not found or inactive.', [
                'email' => $email,
                'path' => $request->path(),
            ]);

            return $next($request);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $next($request);
    }
}
