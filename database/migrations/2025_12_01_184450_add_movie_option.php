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
        Schema::create('movie_version_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movieVersionId')->constrained('movie_versions')->onDelete('cascade');
            $table->foreignId('movieOptionId')->constrained('options')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_version_options');
    }
};
