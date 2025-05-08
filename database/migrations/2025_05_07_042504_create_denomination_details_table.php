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
        Schema::create('denomination_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('den_id')->constrained('denomination');
            $table->integer('denomination_1000')->default(0);
            $table->integer('denomination_500')->default(0);
            $table->integer('denomination_200')->default(0);
            $table->integer('denomination_100')->default(0);
            $table->integer('denomination_50')->default(0);
            $table->integer('denomination_20')->default(0);
            $table->integer('denomination_10')->default(0);
            $table->integer('denomination_5')->default(0);
            $table->integer('denomination_1')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denomination_details');
    }
};
