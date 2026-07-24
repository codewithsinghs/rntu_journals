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
        Schema::create('editorial_board', function (Blueprint $table) {
            $table->id();

            // Role / Badge (Editor-in-Chief, Managing Editor, Executive Editor, Editors, Associate Editors, Members)
            $table->string('role');                             // e.g. Editor-in-Chief

            // Personal Info
            $table->string('name');                            // e.g. Dr. Rajendra Sir Ph.D
            $table->string('designation')->nullable();         // e.g. Assistant Professor in Physics
            $table->string('department')->nullable();          // e.g. Department of Education in Science
            $table->string('institute')->nullable();           // e.g. Regional Institute of Education
            $table->string('university_or_org')->nullable();   // e.g. NCERT
            $table->string('city')->nullable();                // e.g. Mysore 570006, Karnataka, India
            $table->string('email')->nullable();               // e.g. rjrnj@journals.asianressc.org

            // Profile Links
            $table->string('orcid_url')->nullable();           // ORCID profile URL
            $table->string('scopus_url')->nullable();          // Scopus profile URL
            $table->string('web_of_science_url')->nullable();  // Web of Science profile URL

            // Extra column
            $table->string('profile_image')->nullable();       // Profile photo path

            // Status & Ordering
            $table->boolean('is_active')->default(true);
            $table->integer('sequence')->default(0);           // for ordering within a role group

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('editorial_board');
    }
};