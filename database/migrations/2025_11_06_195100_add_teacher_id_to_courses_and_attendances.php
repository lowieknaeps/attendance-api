<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('id');
            $table->string('teacher_name')->nullable()->after('teacher_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['teacher_id', 'teacher_name']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('teacher_id');
        });
    }
};
