<?php

namespace App\Providers;

use App\Facades\Telegram;
use App\Telegram\Bot\Factory;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //-
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        $this->app->bind(Telegram::class, function() {
            return new Factory();
        });

        $this->app->bind(Webhook::class, function () use ($request) {
            return new Webhook($request, new Telegram());
        });
    }
}
