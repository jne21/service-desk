<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Services\TicketChangeLogger;
use App\Services\Contracts\TicketChangeLoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TicketChangeLoggerInterface::class,
            TicketChangeLogger::class
        );
    }
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('ticket-import', function (Request $request) {
            $source = $request->attributes->get('ticket_source');

            return Limit::perMinute(30)->by(
                $source ? 'source:'.$source->id : 'ip:'.$request->ip()
            );
        });
    }
}
