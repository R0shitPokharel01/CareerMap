<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roadmap_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('roadmap_id');
            $table->integer('percent_complete')->default(0);
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'roadmap_id']);
            $table->timestamps();
        });

        Schema::create('user_task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('roadmap_id');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'task_id']);
            $table->timestamps();
        });

        Schema::create('user_streak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->integer('current_streak')->default(0);
            $table->date('last_active_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_streak');
        Schema::dropIfExists('user_task_progress');
        Schema::dropIfExists('user_roadmap_progress');
    }
};
