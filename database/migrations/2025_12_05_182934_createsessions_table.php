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
        Schema::dropIfExists('sessions');
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movieVersionId')->constrained('movie_versions')->onDelete('cascade');
            $table->foreignId('roomId')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('cinemaId')->constrained('cinemas')->onDelete('cascade');
            $table->foreignId('movieId')->constrained('movies')->onDelete('cascade');
            $table->string('status')->default('draft');
            $table->dateTime('startTime');
            $table->dateTime('endTime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
