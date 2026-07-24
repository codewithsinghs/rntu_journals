<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->date('published_date')->nullable()->after('year');
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('articles_count');
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->integer('articles_count')->nullable()->default(0)->after('year');
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('published_date');
        });
    }
};