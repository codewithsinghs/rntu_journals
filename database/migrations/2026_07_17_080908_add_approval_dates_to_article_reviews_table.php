<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('article_reviews', function (Blueprint $table) {
            $table->timestamp('forwarded_to_reviewer_date')->nullable()->after('reviewer_id');
            $table->timestamp('reviewer_approval_date')->nullable()->after('reviewer_status');
            $table->timestamp('approval_date')->nullable()->after('editor_status');
        });
    }

    public function down()
    {
        Schema::table('article_reviews', function (Blueprint $table) {
            $table->dropColumn(['forwarded_to_reviewer_date', 'reviewer_approval_date', 'approval_date']);
        });
    }
};