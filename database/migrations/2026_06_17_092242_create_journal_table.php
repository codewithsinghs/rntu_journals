<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal', function (Blueprint $table) {
            $table->id();

            // ===== Common / Merged Fields =====
            $table->string('title');                         // journals.title + journal.name
            $table->text('description')->nullable();         // common in both
            $table->string('cover_image')->nullable();        // journals.cover_image + journal.cover_image_url
            $table->boolean('is_active')->default(true);      // common in both

            // ===== Unique to journals table =====
            $table->string('view_all_issues_label')->default('View All Issues');
            $table->string('view_all_issues_link')->nullable();

            $table->string('explore_journals_label')->default('Explore Journals');
            $table->string('explore_journals_link')->nullable();

            $table->string('title_2')->nullable();
            $table->json('fields_covered')->nullable();
            $table->integer('sequence')->default(0);

            // ===== Unique to journal table =====
            $table->string('abbreviation')->nullable();

            // ISSN details
            $table->string('e_issn')->nullable();
            $table->string('p_issn')->nullable();
            $table->string('issn_online')->nullable();

            // Volume / Issue
            $table->string('volume')->nullable();
            $table->string('issue')->nullable();
            $table->string('latest_volume')->nullable();

            // Publishing info
            $table->string('publication_language')->nullable();
            $table->string('publishing_frequency')->nullable();
            $table->string('publishing_months')->nullable();

            // Indexing
            $table->string('indexing_impact_factor')->nullable();

            // Review timeline
            $table->string('time_to_first_decision')->nullable();
            $table->string('time_to_review')->nullable();
            $table->string('acceptance_to_publication')->nullable();

            // Content
            $table->text('aim_and_scope_title')->nullable();
            $table->text('aim_and_scope')->nullable();
            $table->string('badge')->nullable();
            $table->string('article_template_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal');
    }
};