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
        Schema::create('contact', function (Blueprint $table) {
            $table->id();

               // ── Contact ──────────────────────────────
            $table->string('contact_badge');           // "Contact Title"
            $table->string('contact_heading1');         // "Contact Heading"
            $table->text('contact_detail1');            // "Contact Details"

            $table->string('contact_heading2');         // "Contact Heading"
            $table->text('contact_detail2');            // "Contact Details"

            $table->string('contact_heading3');         // "Contact Heading"
            $table->text('contact_detail3');            // "Contact Details"


            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact');
    }
};
