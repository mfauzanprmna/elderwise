<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nomor urut kelulusan + format nomor sertifikat yang bisa diatur admin (kode otomatis {TAHUN} dan {URUT}).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (! Schema::hasColumn('certificates', 'sequence')) {
                $table->unsignedInteger('sequence')->nullable()->index();
            }
        });

        Schema::table('certificate_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('certificate_settings', 'number_template')) {
                $table->string('number_template', 120)->default('EW/{TAHUN}/{URUT}');
            }
            if (! Schema::hasColumn('certificate_settings', 'number_reset_yearly')) {
                $table->boolean('number_reset_yearly')->default(true);
            }
        });

        // Sertifikat yang sudah terbit diberi nomor urut sesuai urutan terbitnya (mulai dari 1 tiap tahun),
        // sama dengan cara penomoran sebelumnya sehingga nomor "EW/tahun/urut" tidak berubah.
        $perYear = [];
        DB::table('certificates')->whereNull('sequence')->orderBy('issued_at')->orderBy('id')->get()
            ->each(function ($row) use (&$perYear) {
                $year = Carbon::parse($row->issued_at)->year;
                $perYear[$year] = ($perYear[$year] ?? (int) DB::table('certificates')->whereNotNull('sequence')
                    ->whereYear('issued_at', $year)->max('sequence')) + 1;
                DB::table('certificates')->where('id', $row->id)->update(['sequence' => $perYear[$year]]);
            });
    }

    public function down(): void
    {
        Schema::table('certificate_settings', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_settings', 'number_reset_yearly')) {
                $table->dropColumn('number_reset_yearly');
            }
        });
        Schema::table('certificate_settings', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_settings', 'number_template')) {
                $table->dropColumn('number_template');
            }
        });
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'sequence')) {
                $table->dropIndex(['sequence']);
            }
        });
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'sequence')) {
                $table->dropColumn('sequence');
            }
        });
    }
};
