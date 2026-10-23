<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Verifikasi email: halaman pemberitahuan -> klik tautan di email -> akun aktif.
 * Pengguna yang belum terverifikasi tidak bisa membuka dashboard & materi (middleware "verified").
 */
class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email', [
            'email' => $request->user()->email,
            'menit' => (int) config('auth.verification.expire', 60),
        ]);
    }

    /** Dibuka dari tautan di email. Tautan bertanda tangan (signed) dan berlaku terbatas. */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill(); // menandai terverifikasi (aman jika sudah pernah)

        return redirect()->route('dashboard')
            ->with('success', 'Email berhasil diverifikasi. Selamat belajar!');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            report($e); // biasanya pengaturan email (SMTP) belum benar

            return back()->with('mail_error', 'Email belum dapat dikirim saat ini. Silakan coba lagi nanti atau hubungi penyelenggara.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
