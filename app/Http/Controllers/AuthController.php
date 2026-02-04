<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Maximum login attempts before lockout
     */
    protected int $maxAttempts = 5;
    
    /**
     * Lockout duration in seconds
     */
    protected int $decaySeconds = 300; // 5 minutes

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Check rate limiting
        $this->checkRateLimiter($request);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Check if user is active
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
                ])->onlyInput('email');
            }

            // Clear rate limiter on success
            RateLimiter::clear($this->throttleKey($request));
            
            $request->session()->regenerate();

            // Log successful login
            Log::channel('auth')->info('Successful login', [
                'user_id' => Auth::id(),
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended('dashboard');
        }

        // Increment rate limiter on failure
        RateLimiter::hit($this->throttleKey($request), $this->decaySeconds);

        // Log failed login attempt
        Log::channel('auth')->warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempts_remaining' => RateLimiter::remaining($this->throttleKey($request), $this->maxAttempts),
        ]);

        return back()->withErrors([
            'email' => 'Kombinasi email dan password tidak cocok.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log logout
        Log::channel('auth')->info('User logged out', [
            'user_id' => $userId,
            'ip' => $request->ip(),
        ]);

        return redirect('/login');
    }

    /**
     * Check rate limiter for login attempts
     */
    protected function checkRateLimiter(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));

            // Log lockout
            Log::channel('security')->warning('Login rate limit exceeded', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'lockout_seconds' => $seconds,
            ]);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }
    }

    /**
     * Generate throttle key for rate limiting
     */
    protected function throttleKey(Request $request): string
    {
        return 'login:' . $request->ip() . '|' . strtolower($request->input('email'));
    }
}

