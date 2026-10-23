<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waivers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submit_articles_id')
                ->constrained('submit_articles')
                ->cascadeOnDelete();

            $table->text('reason');
            $table->decimal('partial_payment', 10, 2)->nullable();
            $table->decimal('full_payment', 10, 2)->nullable();
            $table->decimal('payable_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waivers');
    }
};