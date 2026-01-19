<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'course_id')) {
                $table->string('course_id')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'group')) {
                $table->string('group')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'lesson')) {
                $table->string('lesson')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'room')) {
                $table->string('room')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'device_id')) {
                $table->string('device_id')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'location')) {
                $table->string('location')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'source')) {
                $table->string('source')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'course_id',
                'group',
                'lesson',
                'room',
                'device_id',
                'location',
                'source',
                'notes',
            ]);
        });
    }
};
