<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submit_articles', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('issue_id');
            $table->timestamp('hidden_at')->nullable()->after('is_hidden');
            $table->unsignedBigInteger('hidden_by')->nullable()->after('hidden_at');
            $table->softDeletes(); // adds nullable deleted_at
        });
    }

    public function down(): void
    {
        Schema::table('submit_articles', function (Blueprint $table) {
            $table->dropColumn(['is_hidden', 'hidden_at', 'hidden_by']);
            $table->dropSoftDeletes();
        });
    }
};