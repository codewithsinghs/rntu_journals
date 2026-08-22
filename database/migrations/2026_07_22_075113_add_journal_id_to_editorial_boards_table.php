<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editorial_board', function (Blueprint $table) {
            $table->foreignId('journal_id')
                ->nullable()
                ->after('id')
                ->constrained('journals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('editorial_board', function (Blueprint $table) {
            $table->dropForeign(['journal_id']);
            $table->dropColumn('journal_id');
        });
    }
};