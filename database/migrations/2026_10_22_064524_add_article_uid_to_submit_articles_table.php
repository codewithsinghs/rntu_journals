<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submit_articles', function (Blueprint $t) {
            $t->string('article_uid', 30)->nullable()->unique()->after('id');
        });

        DB::table('submit_articles')
            ->whereNull('article_uid')
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('submit_articles')->where('id', $row->id)->update([
                    'article_uid' => 'ART-' . date('Y', strtotime($row->created_at))
                        . '-' . str_pad($row->id, 5, '0', STR_PAD_LEFT),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('submit_articles', function (Blueprint $t) {
            $t->dropUnique(['article_uid']);
            $t->dropColumn('article_uid');
        });
    }
};