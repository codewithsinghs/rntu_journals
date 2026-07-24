<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_basic_contents', function (Blueprint $table) {
            $table->id();

            // ── Hero / About Section ──────────────────────────────
            $table->string('about_badge');           // "ABOUT"
            $table->string('about_heading');         // "RNTU Journals"
            $table->text('about_description_1');     // First paragraph
            $table->text('about_description_2');     // Second paragraph

            $table->string('about_section_img1'); 
            $table->string('about_section_img2');  
            // ── Why Researchers Trust Us Section ──────────────────
            $table->string('why_badge');             // "PUBLISHING"
            $table->string('why_heading');           // "Why Researchers Trust Us"
            $table->text('why_description_1');       // First paragraph
            $table->text('why_description_2');       // Second paragraph

            // ── Side Image ────────────────────────────────────────
            $table->string('why_section_image');     // Left image

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_basic_contents');
    }
};