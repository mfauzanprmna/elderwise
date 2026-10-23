<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@elderwise.test')->exists()) {
            $admin = new User([
                'name' => 'ElderWiSE Admin',
                'email' => 'admin@elderwise.test',
                'password' => 'password123',
                'role' => 'admin',
            ]);
            // Email contoh ini tidak bisa menerima pesan, jadi langsung ditandai terverifikasi.
            $admin->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
