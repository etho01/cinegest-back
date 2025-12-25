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
        Schema::dropIfExists('movie_caches');
        Schema::create('movie_caches', function (Blueprint $table) {
            $table->id();
            $table->string('externalId')->unique();
            $table->string('title');
            $table->string('posterUrl')->nullable();
            $table->date('releaseDate')->nullable();
            $table->json('genres')->nullable();
            $table->string('director')->nullable();
            $table->string('duration')->nullable();
            $table->string('ageRating')->nullable();
            $table->text('description')->nullable();
            $table->string('logoUrl')->nullable();
            $table->string('trailerUrl')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('ratingCount')->nullable();
            $table->json('cast')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_caches');
    }
};
