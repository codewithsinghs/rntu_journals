<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_basic_contents', function (Blueprint $table) {
            $table->string('about_badge')->nullable()->change();
            $table->text('why_badge')->nullable()->change();
        });

        Schema::table('guidelines', function (Blueprint $table) {
            $table->string('author_badge')->nullable()->change();
            $table->string('process_badge')->nullable()->change();
            $table->string('manuscript_badge')->nullable()->change();
            $table->string('formatting_badge1')->nullable()->change();
            $table->string('formatting_badge2')->nullable()->change();
            $table->string('layout_badge1')->nullable()->change();
            $table->string('acknowlegdement_badge1')->nullable()->change();
        });
         Schema::table('home_basic_contents', function (Blueprint $table) {
            $table->string('aim_and_scope_title_1')->nullable()->change();
            $table->string('why_rntu_title_1')->nullable()->change();
            $table->string('latest_journal_title')->nullable()->change();
        });
    }

    public function down(): void
    {
         Schema::table('about_basic_contents', function (Blueprint $table) {
            $table->string('about_badge')->nullable(false)->change();
            $table->text('why_badge')->nullable(false)->change();
        });

        Schema::table('guidelines', function (Blueprint $table) {
            $table->string('author_badge')->nullable(false)->change();
            $table->string('process_badge')->nullable(false)->change();
            $table->string('manuscript_badge')->nullable(false)->change();
            $table->string('formatting_badge1')->nullable(false)->change();
            $table->string('formatting_badge2')->nullable(false)->change();
            $table->string('layout_badge1')->nullable(false)->change();
            $table->string('acknowlegdement_badge1')->nullable(false)->change();
        });
         Schema::table('home_basic_contents', function (Blueprint $table) {
            $table->string('aim_and_scope_title_1')->nullable(false)->change();
            $table->string('why_rntu_title_1')->nullable(false)->change();
            $table->string('latest_journal_title')->nullable(false)->change();
        });
    }
};