<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submit_articles', function (Blueprint $table) {
            $table->string('signature_img')->nullable()->after('author_signature');
            $table->text('references')->nullable()->after('signature_img');
        });
    }

    public function down(): void
    {
        Schema::table('submit_articles', function (Blueprint $table) {
            $table->dropColumn(['signature_img', 'references']);
        });
    }
};