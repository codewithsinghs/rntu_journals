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
        Schema::create('about_basic_content', function (Blueprint $table) {
            $table->id();
            $table->text('tile_1');
            $table->text('heading_1');
            $table->text('journal_desc_1');
             $table->text('journal_desc_2');
            $table->string('journal_img_1')->nullable();
            $table->string('journal_img_2')->nullable();
            $table->text('tile_2');
            $table->text('heading_2');
            $table->text('why_researcher_desc_1');
            $table->text('why_researcher_desc_2');
            $table->json('meta')->nullable();   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_basic_content');
    }
};
