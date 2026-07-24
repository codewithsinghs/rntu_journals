<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * settings_media: a flexible key -> media mapping.
     * Lets `settings` reference ANY number of media items (logo, favicon,
     * og_image, brochure_pdf, price_list_xlsx, anything) without ever
     * needing another migration when a new "slot" is introduced.
     *
     * Example rows:
     *   settings_id | key            | media_id
     *   1           | logo           | 42
     *   1           | favicon        | 43
     *   1           | brochure_pdf   | 51
     */
    public function up(): void
    {
        Schema::create('settings_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('settings_id')
                ->constrained('settings')
                ->cascadeOnDelete();

            $table->string('key'); // e.g. 'logo', 'favicon', 'og_image', 'brochure_pdf'

            $table->foreignId('media_id')
                ->constrained('medias')
                ->cascadeOnDelete();

            $table->timestamps();

            // One value per key per settings row (re-assigning 'logo' replaces, not duplicates)
            $table->unique(['settings_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_media');
    }
};