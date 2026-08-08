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
        Schema::table('guidelines', function (Blueprint $table) {
            $table->foreignId('journal_id')
                ->nullable()
                ->after('id')
                ->constrained('journal') // references id on the `journal` table
                ->nullOnDelete();        // set to null if the journal is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guidelines', function (Blueprint $table) {
            $table->dropForeign(['journal_id']);
            $table->dropColumn('journal_id');
        });
    }
};