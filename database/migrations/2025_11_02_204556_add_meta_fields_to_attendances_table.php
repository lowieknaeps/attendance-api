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
            $table->string('course_id')->nullable();
            $table->string('group')->nullable();
            $table->string('lesson')->nullable();
            $table->string('room')->nullable();
            $table->string('device_id')->nullable();
            $table->string('location')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
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
