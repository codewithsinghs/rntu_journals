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
        Schema::create('article_reviewers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submit_article_id')
                ->constrained('submit_articles')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('email');
            $table->string('institution');
            $table->string('area_of_expertise');

            // Preserves the order in which reviewers were added
            $table->unsignedSmallInteger('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_reviewers');
    }
};