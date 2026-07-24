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
        Schema::create('submit_articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            // Ownership
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Corresponding author details
            $table->string('full_name');
            $table->string('mobile_no', 15);
            $table->string('email');
            $table->string('affiliating_institute');
            $table->string('department');
            $table->string('orcid_id')->nullable();
            $table->text('affiliating_institute_address');

            // Abstract / manuscript details
            $table->foreignId('journal_id')->nullable()->constrained('journal')->nullOnDelete();
            $table->string('manuscript_title');
            $table->text('abstract_summary');
            $table->json('keywords');
            $table->string('signed_manuscript_pdf');
            $table->string('abstract_file');
            $table->string('signature_file');

            // Declaration
            $table->json('declarations');

            // Copyright
            $table->string('author_signature');
            $table->date('submission_date');
            $table->boolean('terms_accepted')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submit_articles');
    }
};
