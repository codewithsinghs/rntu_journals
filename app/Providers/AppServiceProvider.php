<?php

namespace App\Providers;

use App\Http\ViewComposers\AboutComposer;
use App\Http\ViewComposers\AnnouncementsComposer;
use App\Http\ViewComposers\ContactComposer;
use App\Http\ViewComposers\EditorialBoardComposer;
use App\Http\ViewComposers\FooterComposer;
use App\Http\ViewComposers\GuidelinesComposer;
use App\Http\ViewComposers\HeaderComposer;
use App\Http\ViewComposers\JournalsComposer;
use App\Http\ViewComposers\LatestJournalComposer;
use App\Http\ViewComposers\MetaComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Header
        View::composer('frontend.partials.header',          HeaderComposer::class);
        View::composer('frontend.partials.AllPageHeader',   HeaderComposer::class); // reuses same composer

        // Meta tags for all frontend pages
        View::composer([
            '*',
            'frontend.home',
            'frontend.partials.*',
            'frontend.journal.*',
            'frontend.article.*',
        ], MetaComposer::class);

        // Journals
        View::composer('frontend.partials.journals',        JournalsComposer::class);
        View::composer('frontend.partials.latestjournal',        JournalsComposer::class);
        View::composer('frontend.partials.anushandhan',        JournalsComposer::class);

        // Announcements
        View::composer('frontend.partials.announcements',   AnnouncementsComposer::class);

        // Footer + Aim and Scope
        View::composer([
            'frontend.partials.footer',
            'frontend.partials.aimandscope',
        ], FooterComposer::class);

        // About
        View::composer([
            'frontend.partials.aboutjournal',
            'frontend.partials.aboutwhysection',
        ], AboutComposer::class);

         // Guidelines
        View::composer([
            'frontend.partials.guidecart',
        ], GuidelinesComposer::class);

         // Editorial Board
        View::composer(['frontend.partials.editorialboard'], EditorialBoardComposer::class);

        // Contact
        View::composer([
            'frontend.partials.contactdetails',
        ], ContactComposer::class);

        // Article
        View::composer(['frontend.partials.latestjournal'], LatestJournalComposer::class);
        View::composer(['frontend.partials.anushandhan'], LatestJournalComposer::class);
    }
}