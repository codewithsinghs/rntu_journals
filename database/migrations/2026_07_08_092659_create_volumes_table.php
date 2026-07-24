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
        Schema::create('volumes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('journal_id')
                ->constrained('journal')
                ->cascadeOnDelete();

            $table->string('volume');           // e.g. Volume 12
            $table->string('year')->nullable(); // e.g. 2025

            $table->unsignedInteger('issues_count')->default(0);

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
        Schema::dropIfExists('volumes');
    }
};