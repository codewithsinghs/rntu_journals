<?php
// app/Services/SiteSettingService.php

namespace App\Services;

use App\Models\Setting;

class SiteSettingService
{
    public function getLogoData(): array
    {
        $settings = Setting::with('mediaSlots.media')->first();

        return [
            'logo_url'     => $settings?->logo?->url,
            'website_name' => $settings?->website_name,
        ];
    }
}