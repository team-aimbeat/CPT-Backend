<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('workout_id')->nullable();
            $table->date('completed_date');
            $table->timestamps();

            $table->unique(['user_id', 'completed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_completions');
    }
};
