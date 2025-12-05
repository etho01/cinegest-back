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
        Schema::create('storage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storageId')->nullable()->constrained('storages')->onDelete('cascade');
            $table->foreignId('roomId')->nullable()->constrained('rooms')->onDelete('cascade');
            $table->foreignId('movieVersionId')->constrained('movie_versions')->onDelete('cascade');
            $table->foreignId('movieId')->constrained('movies')->onDelete('cascade');
            $table->foreignId('originId')->nullable()->constrained('storages')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_items');
    }
};
