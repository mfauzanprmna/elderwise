<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email "Atur ulang kata sandi" berbahasa Indonesia dengan tampilan besar & jelas.
 * Tautan dibuat oleh kelas induk ($url), kita hanya mengganti isi email.
 */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        // Saat uji lokal (MAIL_MAILER=log), isi email di laravel.log ter-encode dan terpotong.
        // Catat juga tautannya apa adanya supaya mudah disalin. Tidak aktif untuk SMTP.
        if (config('mail.default') === 'log') {
            logger()->info('[Lupa kata sandi] Tautan untuk '.$notifiable->getEmailForPasswordReset().': '.$url);
        }

        return $this->buildMailMessage($url);
    }

    protected function buildMailMessage($url)
    {
        $namaSitus = SiteSetting::current()->site_name ?: config('app.name');
        $menit = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject("Atur ulang kata sandi {$namaSitus}")
            ->view(
                ['emails.reset-password', 'emails.reset-password-text'],
                ['url' => $url, 'namaSitus' => $namaSitus, 'menit' => $menit]
            );
    }
}
