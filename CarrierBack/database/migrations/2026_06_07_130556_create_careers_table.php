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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('description');
            $table->string('category');

            $table->enum('demand', ['Low', 'Medium', 'High']);
            $table->text('demand_reason')->nullable();

            $table->string('salary_range');
            $table->string('salary_period')->default('annual');
            $table->text('salary_note')->nullable();

            $table->string('duration');

            $table->json('skills');
            $table->json('prerequisites')->nullable();
            $table->json('tools')->nullable();
            $table->json('certifications')->nullable();
            $table->json('career_paths')->nullable();

            $table->string('reviewed_by')->default('AI Generated');
            $table->boolean('is_published')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};