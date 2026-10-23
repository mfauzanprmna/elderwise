<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('lesson_progress', 'lesson_progress_user_completed_index')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->index(['user_id', 'completed_at'], 'lesson_progress_user_completed_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('lesson_progress', 'lesson_progress_user_completed_index')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->dropIndex('lesson_progress_user_completed_index');
            });
        }
    }
};
