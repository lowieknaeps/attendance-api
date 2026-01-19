<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: teacher_id NOT NULL maken
        DB::statement('ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NULL');
    }
};
