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
        Schema::create('issues', function (Blueprint $table) {

            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('journal_id')
                ->constrained('journals')
                ->cascadeOnDelete();

            $table->foreignId('volume_id')
                ->constrained('volumes')
                ->cascadeOnDelete();

            $table->string('issue');           // e.g. Issue 3
            $table->string('year')->nullable(); // e.g. 2025

            $table->unsignedInteger('articles_count')->default(0);

            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_current')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
