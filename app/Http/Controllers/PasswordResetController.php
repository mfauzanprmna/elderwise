<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Lupa kata sandi: minta tautan lewat email -> buka tautan -> buat kata sandi baru.
 * Memakai password broker bawaan Laravel (tabel password_reset_tokens).
 */
class PasswordResetController extends Controller
{
    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => 'required|email'],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email belum benar. Contoh: nama@kampus.ac.id',
            ]
        );

        $email = (string) $request->input('email');

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            // Biasanya karena pengaturan email (SMTP) belum benar.
            report($e);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email belum dapat dikirim saat ini. Silakan coba lagi nanti atau hubungi penyelenggara.');
        }

        // Email dicek ke database oleh Laravel: terdaftar -> tautan dikirim, tidak terdaftar -> diberi tahu jelas.
        if ($status === PasswordBroker::RESET_LINK_SENT) {
            $menit = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return back()->with(
                'success',
                "Tautan untuk membuat kata sandi baru sudah kami kirim ke {$email}. Silakan buka email Anda (periksa juga folder spam). Tautan berlaku {$menit} menit."
            );
        }

        if ($status === PasswordBroker::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->with('warning', 'Tautan baru saja dikirim ke email tersebut. Mohon tunggu sekitar satu menit sebelum meminta lagi, dan periksa folder spam.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => "Akun dengan email {$email} belum terdaftar. Periksa kembali penulisan email Anda, atau daftar akun baru."]);
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'token' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email belum benar.',
                'password.required' => 'Kata sandi baru wajib diisi.',
                'password.min' => 'Kata sandi baru minimal 8 karakter.',
                'password.confirmed' => 'Ulangi kata sandi belum sama dengan kata sandi baru.',
            ]
        );

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                DB::transaction(function () use ($user, $password) {
                    // Kolom password memakai cast "hashed", jadi otomatis di-hash saat disimpan.
                    $user->forceFill([
                        'password' => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    if (config('session.driver') === 'database') {
                        DB::table(config('session.table', 'sessions'))
                            ->where('user_id', $user->id)
                            ->delete();
                    }

                    // Membuka tautan dari email membuktikan pemilik email ini, jadi sekalian terverifikasi.
                    if (! $user->hasVerifiedEmail()) {
                        $user->markEmailAsVerified();
                    }
                });

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.');
        }

        // Token salah/kedaluwarsa/sudah dipakai, atau email tidak cocok dengan tautan (pesan sengaja disamakan).
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Tautan ini sudah tidak berlaku atau sudah dipakai. Silakan minta tautan baru.']);
    }
}
