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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('icon')->default('emoji_events'); //Material symbol icon name
            $table->string('color')->default('#3525cd'); //hex color for UI card

            $table->enum('type',[
                'task_completion',
                'roadmap_completion',
                'roadmap_progress',
                'streak',
                'profile_complete',
            ]);

            //Flexible JSON- admin defines the rules
            //Example:
            //task_completion: {"count": 5 }
            //roadmap_completion: {"roadmap_id": 1 }
            //roadmap_progress: {"roadmap_id": 1, "progress": 50 }
            //streak: {"days": 7 }
            //profile_complete: {}
            $table->json('condition');
            $table->integer('points')->default(10);        
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};