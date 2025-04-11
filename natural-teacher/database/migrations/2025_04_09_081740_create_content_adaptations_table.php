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
        Schema::create('content_adaptations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('age_group_id')->constrained()->onDelete('cascade');
            $table->string('content_type');
            $table->integer('vocabulary_limit')->nullable();
            $table->integer('sentence_length')->nullable();
            $table->boolean('use_illustrations')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_adaptations');
    }
};
