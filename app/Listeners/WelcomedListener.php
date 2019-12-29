<?php

namespace App\Listeners;

use App\Events\Welcomed;
use App\Services\AMQPService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class WelcomedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public static $count = 0;

    public AMQPChannel $channel;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->channel = app('pub-channel');
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(Welcomed $event)
    {
        $body = json_encode(['message' => 'Hello, world!']);
        $message = new AMQPMessage($body, [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        app(AMQPService::class)->publish($message, 'app.hello');
    }
}
