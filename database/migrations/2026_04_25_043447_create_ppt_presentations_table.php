<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppt_presentations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('slides_count')->default(0);
            $table->string('uploaded_at');
            $table->text('thumbnail_url')->nullable();
            $table->longText('source_text')->nullable();
            $table->longText('slide_data')->nullable();
            $table->string('template_id')->nullable();
            $table->longText('background_image_url')->nullable();
            $table->string('source_type')->nullable();
            $table->string('original_file_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppt_presentations');
    }
};
