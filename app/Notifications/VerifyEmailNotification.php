<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email "Verifikasi alamat email" berbahasa Indonesia dengan tampilan besar & jelas.
 * Tautan bertanda tangan (signed URL) dibuat oleh kelas induk.
 */
class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        // Uji lokal (MAIL_MAILER=log): isi email di laravel.log ter-encode & terpotong,
        // jadi catat juga tautannya apa adanya agar mudah disalin. Tidak aktif untuk SMTP.
        if (config('mail.default') === 'log') {
            logger()->info('[Verifikasi email] Tautan untuk '.$notifiable->getEmailForVerification().': '.$url);
        }

        return $this->buildMailMessage($url);
    }

    protected function buildMailMessage($url)
    {
        $namaSitus = SiteSetting::current()->site_name ?: config('app.name');
        $menit = (int) config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject("Verifikasi email Anda di {$namaSitus}")
            ->view(
                ['emails.verify-email', 'emails.verify-email-text'],
                ['url' => $url, 'namaSitus' => $namaSitus, 'menit' => $menit]
            );
    }
}
