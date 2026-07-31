<?php

namespace App\Providers;


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

        // Meta tags for all frontend pages
        View::composer([
            '*',
            'frontend.home',
            'frontend.partials.*',
            'frontend.journal.*',
            'frontend.article.*',
        ], MetaComposer::class);
    }
}