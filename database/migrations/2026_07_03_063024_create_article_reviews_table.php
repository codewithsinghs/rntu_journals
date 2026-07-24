<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_reviews', function (Blueprint $table) {
            $table->id();

            // One workflow record per submission
            $table->foreignId('submit_article_id')
                ->constrained('submit_articles')
                ->cascadeOnDelete();

            // ── Editor stage ────────────────────────────────────────
            $table->foreignId('editor_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // pending | forwarded_to_reviewer | sent_back_to_author | approved | rejected
            $table->string('editor_status')->default('pending');
            $table->text('editor_remarks')->nullable();

            // ── Reviewer stage ──────────────────────────────────────
            $table->foreignId('reviewer_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // null | correction_needed | no_correction_needed
            $table->string('reviewer_status')->nullable();
            $table->text('reviewer_remarks')->nullable();

            // ── Full manuscript text ────────────────────────────────
            $table->string('full_text')->nullable();

            // ── Overall outcome ──────────────────────────────────────
            // pending | approved | rejected
            $table->string('final_status')->default('pending');

            // ── Workflow position ────────────────────────────────────
            // pending_editor | with_reviewer | reviewer_sent_to_editor | with_author
            // | editor_approved | awaiting_payment | payment_failed | paid | published | rejected
            $table->string('current_stage')->default('pending_editor');

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('revision_count')->default(0);

            $table->timestamps();

            $table->index(['submit_article_id']);
            $table->index(['current_stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_reviews');
    }
};