<?php

namespace App\Providers;

use App\Events\Welcomed;
use App\Listeners\WelcomedListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ],
        Welcomed::class => [
            WelcomedListener::class
        ]
    ];
}
