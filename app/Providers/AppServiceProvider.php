<?php

namespace App\Providers;

use App\Services\AMQPService;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPSocketConnection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AMQPService::class, AMQPService::class);
    }

    public function boot(AMQPService $amqpService)
    {
        $connection = $amqpService->connect();

        $this->app->singleton('amqp', function () use ($connection) {
            return $connection;
        });

        $this->app->singleton('pub-channel', function () use ($connection) {
            return $connection->channel(1);
        });

        $this->app->singleton('sub-channel', function () use ($connection) {
            return $connection->channel();
        });

        $amqpService->boot();
    }
}
