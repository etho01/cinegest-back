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
        Schema::create('cinema_apis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entityId')->constrained('entities')->onDelete('cascade');
            $table->string('apiKey')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cinema_apis_cinema', function (Blueprint $table) {
            $table->foreignId('cinemaApiId')->constrained('cinema_apis')->onDelete('cascade');
            $table->foreignId('cinemaId')->constrained('cinemas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cinema_apis_cinema');
        Schema::dropIfExists('cinema_apis');
    }
};
