<?php

namespace App\Providers;

use App\Services\AMQPService;
use Illuminate\Support\ServiceProvider;

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

        $this->app->singleton('amqp', function () {
            return app(AMQPService::class);
        });

        $this->app->singleton('pub-channel', function () {
            return app('amqp')->getPubChannel();
        });

        $this->app->singleton('sub-channel', function () {
            return app('amqp')->getSubChannel();
        });
    }

    public function boot(AMQPService $amqpService)
    {
        $amqpService->boot();
    }
}
