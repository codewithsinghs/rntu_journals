<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volumes', function (Blueprint $table) {
            $table->dropColumn('issues_count');
        });
    }

    public function down(): void
    {
        Schema::table('volumes', function (Blueprint $table) {
            $table->integer('issues_count')->nullable()->default(0);
        });
    }
};