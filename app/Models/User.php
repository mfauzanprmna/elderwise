<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }

    /** Kirim email "lupa kata sandi" berbahasa Indonesia (bukan bawaan Laravel yang berbahasa Inggris). */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /** Kirim email "verifikasi email" berbahasa Indonesia. */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /** Alamat foto profil: foto yang diunggah sendiri diutamakan, lalu foto akun Google, selain itu null (tampil inisial). */
    public function avatarUrl(): ?string
    {
        if ($this->avatar_path) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return $this->avatar_url ?: null;
    }

    /** Inisial untuk avatar bila belum ada foto, mis. "Budi Santoso" -> "BS". */
    public function initials(): string
    {
        $words = preg_split('/\s+/u', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $letters = collect($words)->take(2)->map(fn ($w) => Str::upper(Str::substr($w, 0, 1)))->implode('');

        return $letters !== '' ? $letters : '?';
    }

    public function lessonProgress() { return $this->hasMany(LessonProgress::class); }
    public function certificate() { return $this->hasOne(Certificate::class); }
}
