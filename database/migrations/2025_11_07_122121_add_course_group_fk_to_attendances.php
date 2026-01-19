<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $t) {
            // course_fk_id
            if (! Schema::hasColumn('attendances', 'course_fk_id')) {
                $t->foreignId('course_fk_id')->nullable()->constrained('courses');
            }

            // group_fk_id
            if (! Schema::hasColumn('attendances', 'group_fk_id')) {
                $t->foreignId('group_fk_id')->nullable()->constrained('groups');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $t) {
            // drop FK + column alleen als ze bestaan
            if (Schema::hasColumn('attendances', 'course_fk_id')) {
                $t->dropConstrainedForeignId('course_fk_id');
            }

            if (Schema::hasColumn('attendances', 'group_fk_id')) {
                $t->dropConstrainedForeignId('group_fk_id');
            }
        });
    }
};
