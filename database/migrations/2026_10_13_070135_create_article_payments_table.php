<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('article_payments');

        Schema::create('article_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submit_article_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('gateway')->default('paytm');
            $table->string('order_id')->unique();
            $table->string('txn_id')->nullable();
            $table->string('bank_txn_id')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('failure_reason')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status')->default('created'); 
            $table->json('response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_payments');
    }
};