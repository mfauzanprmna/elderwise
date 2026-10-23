<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Verifikasi email mulai berlaku untuk pendaftar BARU. Akun yang sudah ada sebelum fitur ini
// dianggap terverifikasi supaya peserta lama tidak tiba-tiba terkunci dari dashboard.
// (Ingin memaksa semua akun lama verifikasi ulang? Hapus migration ini sebelum `php artisan migrate`,
//  atau jalankan: UPDATE users SET email_verified_at = NULL WHERE role != 'admin';)
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Tidak ada yang perlu dikembalikan.
    }
};
