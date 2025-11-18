<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('attendances', function (Blueprint $t) {
            $t->foreignId('course_fk_id')->nullable()->after('course_name')->constrained('courses');
            $t->foreignId('group_fk_id')->nullable()->after('group')->constrained('groups');
        });
    }
    public function down(): void {
        Schema::table('attendances', function (Blueprint $t) {
            $t->dropConstrainedForeignId('course_fk_id');
            $t->dropConstrainedForeignId('group_fk_id');
        });
    }
};
