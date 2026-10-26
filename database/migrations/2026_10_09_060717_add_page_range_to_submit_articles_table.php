<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submit_articles', function (Blueprint $table) {
            $table->unsignedInteger('page_start')->nullable()->after('manuscript_title');
            $table->unsignedInteger('page_end')->nullable()->after('page_start');
        });
    }

    public function down(): void
    {
        Schema::table('submit_articles', function (Blueprint $table) {
            $table->dropColumn(['page_start', 'page_end']);
        });
    }
};