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
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('monitor_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('permission_level', ['view', 'interact', 'manage'])->default('view');
            $table->json('notification_preferences')->nullable();
            $table->timestamps();
            
            // Ensure unique student-monitor relationships
            $table->unique(['student_id', 'monitor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring');
    }
};
