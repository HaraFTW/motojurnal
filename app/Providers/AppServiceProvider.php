<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
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

        // New login/session: allow oil toasts again until the user closes them.
        Event::listen(Login::class, function (): void {
            session()->forget('oil_change_toasts_dismissed');
        });

        View::composer('layouts.app', function ($view): void {
            $expiringReminders = [];
            $oilChangeToasts = [];

            if (auth()->check()) {
                if (! session('oil_change_toasts_dismissed')) {
                    $oilChangeToasts = auth()->user()->oilChangeToastMessages();
                }

                if (! request()->routeIs('reminders.*')) {
                    $user = auth()->user();

                    $reminders = $user->reminders()->expiringSoon()->get()
                        ->concat($user->reminders()->recentlyExpired()->get())
                        ->sortBy('ending_date')
                        ->values();

                    $expiringReminders = $reminders
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
                }
            }

            $view->with([
                'expiringReminders' => $expiringReminders,
                'oilChangeToasts' => $oilChangeToasts,
            ]);
        });
    }
}
