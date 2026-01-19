<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'source')) {
                $table->string('source')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('attendances', 'attendance_session_id')) {
                $table->unsignedBigInteger('attendance_session_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'source')) {
                $table->dropColumn('source');
            }

            if (Schema::hasColumn('attendances', 'attendance_session_id')) {
                $table->dropColumn('attendance_session_id');
            }
        });
    }
};

