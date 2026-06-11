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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->longText('description');
            $table->string('category');

            $table->integer('salary_min');
            $table->integer('salary_max');
            $table->string('salary_period');

            $table->integer('duration_min_months');
            $table->integer('duration_max_months');

            $table->enum('demand',['low','medium','high']);
            $table->string('reviewed_by');
            $table->boolean('is_published');

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
