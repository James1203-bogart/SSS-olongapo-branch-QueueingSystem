<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CouchbaseClient;
use App\Services\CouchbaseTicketRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Optionally register Couchbase services when configured
        $conn = config('couchbase.conn_string');
        if (is_string($conn) && strlen($conn) > 0) {
            try {
                $this->app->singleton(CouchbaseClient::class, function () {
                    return new CouchbaseClient();
                });
                $this->app->singleton(CouchbaseTicketRepository::class, function ($app) {
                    return new CouchbaseTicketRepository($app->make(CouchbaseClient::class));
                });
            } catch (\Throwable $e) {
                // Couchbase SDK not available; skip binding to avoid boot failure
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
