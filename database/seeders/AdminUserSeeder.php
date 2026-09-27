<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@elderwise.test')->exists()) {
            User::create([
                'name' => 'ElderWiSE Admin',
                'email' => 'admin@elderwise.test',
                'password' => 'password123',
                'role' => 'admin',
            ]);
        }
    }
}
