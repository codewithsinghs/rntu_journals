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
        Schema::create('guidelines', function (Blueprint $table) {
            $table->id();
               // ── Guidelines Author ──────────────────────────────
            $table->string('author_badge');           // "Guidelines Title"
            $table->string('author_heading');         // "Guidelines Heading"
            $table->text('author_description');     // "Guidelines paragraph"

            // ── Process Submission ──────────────────────────────
            $table->string('process_badge'); //"Process Title"
            $table->string('process_heading');  //"Process Heading"
            $table->text('process_description');   // "Process paragraph"

            // ── MANUSCRIPT  ──────────────────
            $table->string('manuscript_badge');             // "MANUSCRIPT Title"
            $table->string('manuscript_heading');           // "MANUSCRIPT Heading"
            $table->text('manuscript_description');       // "MANUSCRIPT paragraph"

            // ── DOCUMENT Formatting  ──────────────────────────────
            $table->string('formatting_badge1');            // "Formatting Title 1"
            $table->string('formatting_badge2');           // "Formatting Title 2"
            $table->string('formatting_heading');         // "Formatting Heading"
            $table->text('formatting_description');     // "Formatting paragraph"

             // ── Page Layout  ──────────────────────────────
            $table->string('layout_badge1');             // "Page Layout Title"
            $table->string('layout_heading');         // "Page Layout Heading"
            $table->text('layout_description');     // "Page Layout paragraph"


             // ── Acknowlegdement  ──────────────────────────────
            $table->string('acknowlegdement_badge1');             // "Acknowlegdement Title"
            $table->string('acknowlegdement_heading');         // "Acknowlegdement Heading"
            $table->text('acknowlegdement_description');     // "Acknowlegdement paragraph"


            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guidelines');
    }
};
