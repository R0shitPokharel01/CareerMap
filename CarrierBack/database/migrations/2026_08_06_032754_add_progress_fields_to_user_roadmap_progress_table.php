<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_roadmap_progress', function (Blueprint $table) {
            if (!Schema::hasColumn(
                'user_roadmap_progress',
                'progress_percentage'
            )) {
                $table->unsignedTinyInteger('progress_percentage')
                    ->default(0)
                    ->after('career_id');
            }

            if (!Schema::hasColumn(
                'user_roadmap_progress',
                'status'
            )) {
                $table->string('status')
                    ->default('in_progress')
                    ->after('progress_percentage');
            }

            if (!Schema::hasColumn(
                'user_roadmap_progress',
                'started_at'
            )) {
                $table->timestamp('started_at')
                    ->nullable()
                    ->after('status');
            }

            if (!Schema::hasColumn(
                'user_roadmap_progress',
                'completed_at'
            )) {
                $table->timestamp('completed_at')
                    ->nullable()
                    ->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_roadmap_progress', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn(
                'user_roadmap_progress',
                'progress_percentage'
            )) {
                $columns[] = 'progress_percentage';
            }

            if (Schema::hasColumn(
                'user_roadmap_progress',
                'status'
            )) {
                $columns[] = 'status';
            }

            if (Schema::hasColumn(
                'user_roadmap_progress',
                'started_at'
            )) {
                $columns[] = 'started_at';
            }

            if (Schema::hasColumn(
                'user_roadmap_progress',
                'completed_at'
            )) {
                $columns[] = 'completed_at';
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
