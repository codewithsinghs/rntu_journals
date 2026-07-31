<?php
// app/Services/FooterService.php

namespace App\Services;

use App\Models\Setting;
use App\Models\HomeBasicContent;
use App\Models\MenuItem;

class FooterService
{
    public function getFooterData(): array
    {
        $settings = Setting::with('mediaSlots.media')->first();
        $content  = HomeBasicContent::first();

        return [
            'about_description' => $content?->footer_about_description,

            'settings' => [
                'website_name'   => $settings?->website_name,
                'address'        => $settings?->address,
                'email'          => $settings?->email,
                'phone'          => $settings?->phone,
                'website_url'    => $settings?->website_url,
                'facebook_url'   => $settings?->facebook_url,
                'instagram_url'  => $settings?->instagram_url,
                'twitter_url'    => $settings?->twitter_url,
                'youtube_url'    => $settings?->youtube_url,
                'linkedin_url'   => $settings?->linkedin_url,
            ],

            'useful_links' => $this->menuItemsFor('menu.name', 'Useful Links'),

            'journal_policies' => $this->menuItemsFor('menu.name', 'Journal Policies'),

            'bottom_links' => $this->menuItemsFor('menu.location', 'footer-bottom'),
        ];
    }

    private function menuItemsFor(string $relationField, string $value): array
    {
        [$relation, $column] = explode('.', $relationField);

        return MenuItem::whereHas($relation, fn ($q) => $q->where($column, $value))
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['label', 'url', 'target'])
            ->map(fn ($item) => [
                'label'  => $item->label,
                'url'    => $item->url,
                'target' => $item->target ?? '_self',
            ])
            ->values()
            ->all();
    }
}