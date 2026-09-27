<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_settings', function (Blueprint $t) {
            $t->id();
            $t->string('background_path')->nullable();
            $t->unsignedInteger('background_width')->nullable();
            $t->unsignedInteger('background_height')->nullable();
            // Elemen bawaan (nama, tanggal, nomor, modul) + elemen custom.
            // Disimpan sebagai array JSON, tiap elemen berisi posisi dalam PERSEN
            // terhadap lebar/tinggi gambar asli, supaya tetap presisi di ukuran apa pun.
            $t->json('fields')->nullable();
            $t->string('title')->default('Sertifikat Kelulusan');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('certificate_settings');
    }
};
