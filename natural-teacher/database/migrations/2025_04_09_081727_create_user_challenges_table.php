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
        Schema::create('user_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->integer('progress_percent')->default(0);
            $table->timestamp('completed_date')->nullable();
            $table->integer('points_earned')->default(0);
            $table->timestamps();
            
            // Ensure unique user-challenge combinations
            $table->unique(['user_id', 'challenge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenges');
    }
};
