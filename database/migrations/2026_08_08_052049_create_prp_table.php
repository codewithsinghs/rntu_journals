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
        Schema::create('prp', function (Blueprint $table) {
           $table->id();
            $table->string('author_heading');         // "Guidelines Heading"
            $table->text('author_description');     // "Guidelines paragraph"
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prp');
    }
};
