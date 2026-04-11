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
        Schema::create('songs', function (Blueprint $table) {
            // Gamiton nato ang string kay ang ID sa React kay gikan sa Date.now().toString()
            $table->string('id')->primary(); 
            $table->string('folder_id');
            
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('url');
            
            // Gamiton ang longText kay taas kaayo ang mga lyrics ug chords
            $table->longText('lyrics')->nullable();
            $table->longText('chords')->nullable();
            
            // Para ma-save ang arrangement inig Drag and Drop
            $table->integer('order')->default(0); 
            
            // Foreign Key: Kung i-delete ang folder, ma-delete apil ang mga kanta sa sulod
            $table->foreign('folder_id')
                  ->references('id')
                  ->on('folders')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};