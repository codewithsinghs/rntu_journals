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
        Schema::create('home_basic_contents', function (Blueprint $table) {
            $table->id();

            // ── Aim and Scope Section ──────────────────────────────
            $table->text('aim_and_scope_title_1');
            $table->text('aim_and_scope_title_2');
            $table->text('aim_and_scope_description');
            $table->text('aim_and_scope_title_3');
            $table->text('scope_of_publication_description');
            $table->text('university_highlight_quote');
            $table->string('aim_section_image')->nullable();

            // ── Why RNTU Journals Section ──────────────────────────
            $table->text('why_rntu_title_1');
            $table->text('why_rntu_title_2');
            $table->string('why_rntu_years');
            $table->string('why_rntu_years_label');
            $table->string('why_rntu_articles');
            $table->string('why_rntu_articles_label');
            $table->string('why_rntu_journals');
            $table->string('why_rntu_journals_label');
            $table->string('why_rntu_readers');
            $table->string('why_rntu_readers_label');
            $table->string('why_rntu_access');
            $table->string('why_rntu_access_label');

            // ── Support Section ────────────────────────────────────
            $table->string('support_section_heading');
            $table->string('support_articles_count');
            $table->string('support_short_heading');
            $table->text('support_section_description');

            // ── Latest Journal Issues Section ──────────────────────
            $table->string('latest_journal_title');
            $table->string('latest_journal_heading');
            $table->text('latest_journal_description');
            // ── Footer / About Section ─────────────────────────────
            $table->text('footer_about_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::dropIfExists('home_basic_contents');
}
};
