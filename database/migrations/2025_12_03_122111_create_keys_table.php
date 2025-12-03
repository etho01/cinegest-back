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
        Schema::dropIfExists('keys');
        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cinemaId')->constrained('cinemas')->onDelete('cascade');
            $table->foreignId('roomId')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('movieVersionId')->constrained('movie_versions')->onDelete('cascade');
            $table->date('dateStart');
            $table->date('dateEnd');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keys');
    }
};
