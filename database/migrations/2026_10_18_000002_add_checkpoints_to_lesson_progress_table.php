<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Progres membaca per materi: berapa "checkpoint" (bagian) yang sudah dilewati secara berurutan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            if (! Schema::hasColumn('lesson_progress', 'checkpoint')) {
                $table->unsignedSmallInteger('checkpoint')->default(0);
            }
            if (! Schema::hasColumn('lesson_progress', 'checkpoint_at')) {
                $table->timestamp('checkpoint_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_progress', 'checkpoint_at')) {
                $table->dropColumn('checkpoint_at');
            }
        });
        Schema::table('lesson_progress', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_progress', 'checkpoint')) {
                $table->dropColumn('checkpoint');
            }
        });
    }
};
