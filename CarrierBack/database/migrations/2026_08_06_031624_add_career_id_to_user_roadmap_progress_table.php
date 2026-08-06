<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_roadmap_progress', function (Blueprint $table) {
            $table->foreignId('career_id')
                ->nullable()
                ->after('user_id')
                ->constrained('careers')
                ->cascadeOnDelete();

            $table->unique(
                ['user_id', 'career_id'],
                'user_career_progress_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('user_roadmap_progress', function (Blueprint $table) {
            $table->dropUnique('user_career_progress_unique');
            $table->dropForeign(['career_id']);
            $table->dropColumn('career_id');
        });
    }
};
