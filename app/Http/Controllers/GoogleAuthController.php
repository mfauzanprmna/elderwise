<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Masuk / daftar memakai akun Google.
 *
 * Alur: tombol Google -> redirect() -> halaman izin Google -> callback().
 * - Sudah pernah masuk via Google  -> langsung masuk.
 * - Email sama dengan akun lama    -> akun ditautkan ke Google lalu masuk.
 * - Email belum terdaftar          -> akun peserta baru dibuat otomatis.
 */
class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->fail('Masuk dengan Google belum diaktifkan oleh penyelenggara. Silakan masuk memakai email dan kata sandi.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->fail('Masuk dengan Google belum diaktifkan oleh penyelenggara. Silakan masuk memakai email dan kata sandi.');
        }

        // Pengguna menekan "Batal" di halaman izin Google.
        if ($request->filled('error')) {
            return $this->fail('Proses masuk dengan Google dibatalkan. Silakan coba lagi jika ingin melanjutkan.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Kami belum berhasil terhubung dengan Google. Silakan coba lagi.');
        }

        $googleId = (string) $google->getId();
        $email = $google->getEmail();
        $raw = is_array($google->user ?? null) ? $google->user : [];

        // Hanya percaya email yang sudah diverifikasi oleh Google.
        $emailVerified = filter_var($raw['email_verified'] ?? $raw['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($googleId === '' || blank($email) || ! $emailVerified) {
            return $this->fail('Email pada akun Google tersebut belum terverifikasi sehingga tidak dapat dipakai untuk masuk.');
        }

        $user = User::where('google_id', $googleId)->first();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user && filled($user->google_id) && $user->google_id !== $googleId) {
                return $this->fail('Email ini sudah terhubung dengan akun Google yang berbeda.');
            }

            if ($user) {
                // Akun lama dengan email yang sama: tautkan ke Google.
                $attributes = [
                    'google_id' => $googleId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ];

                if (! $user->hasVerifiedEmail()) {
                    // Akun ini dibuat lewat formulir dan emailnya belum pernah dibuktikan: yang mendaftar dulu belum tentu
                    // pemilik email. Pemilik sebenarnya baru saja terbukti lewat Google, jadi kata sandi lama dicabut
                    // beserta sesi yang masih terbuka. Ia bisa membuat kata sandi baru lewat "Lupa kata sandi".
                    $attributes['password'] = Str::random(40);
                    $attributes['remember_token'] = Str::random(60);

                    if (config('session.driver') === 'database') {
                        DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
                    }
                }

                $user->forceFill($attributes)->save();
            } else {
                // Peserta baru. Kata sandi acak: pengguna dapat membuatnya sendiri lewat "Lupa kata sandi".
                $user = new User([
                    'name' => Str::limit($google->getName() ?: Str::before($email, '@'), 120, ''),
                    'email' => $email,
                    'password' => Str::random(40),
                    'role' => 'learner',
                ]);
                $user->forceFill([
                    'google_id' => $googleId,
                    'email_verified_at' => now(),
                ])->save();
            }
        }

        // Foto profil dari akun Google (hanya alamat https yang wajar panjangnya).
        $avatar = $google->getAvatar();
        if (is_string($avatar) && str_starts_with($avatar, 'https://') && strlen($avatar) <= 500 && $user->avatar_url !== $avatar) {
            $user->forceFill(['avatar_url' => $avatar])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Berhasil masuk dengan Google. Selamat datang, ' . $user->name . '!');
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['google' => $message]);
    }
}
