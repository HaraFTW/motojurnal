<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($rootUrl = config('app.url')) {
            URL::forceRootUrl($rootUrl);
        }

        View::composer('layouts.app', function ($view): void {
            if (! auth()->check() || request()->routeIs('reminders.*')) {
                $view->with('expiringReminders', []);

                return;
            }

            $user = auth()->user();

            $reminders = $user->reminders()->expiringSoon()->get()
                ->concat($user->reminders()->recentlyExpired()->get())
                ->sortBy('ending_date')
                ->values();

            $messages = $reminders
                ->map(function ($reminder) {
                    $isExpired = $reminder->ending_date->copy()->startOfDay()->lt(now()->startOfDay());

                    return [
                        'message' => $isExpired
                            ? $reminder->expiredToastMessage()
                            : $reminder->expirationToastMessage(),
                        'expired' => $isExpired,
                    ];
                })
                ->all();

            $view->with('expiringReminders', $messages);
        });
    }
}
