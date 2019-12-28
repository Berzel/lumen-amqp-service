<?php

namespace App\Listeners;

use App\Events\Welcomed;
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
        $ackCallback = function (AMQPMessage $msg) {
            $msg = json_decode($msg->getBody())->message;
            fprintf(STDOUT, "<--Acknowledged-->\n");
        };

        $nackCallback = function (AMQPMessage $msg) {
            $msg = json_decode($msg->getBody())->message;
            fprintf(STDOUT, "<--Not Acknowledged-->\n");
        };

        $this->channel->confirm_select(false);
        $this->channel->set_ack_handler($ackCallback);
        $this->channel->set_nack_handler($nackCallback);

        $body = json_encode(['message' => 'Hello, world!']);
        $message = new AMQPMessage($body, [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        self::$count++;
        $message->delivery_info['delivery_tag'] = self::$count;
        $this->channel->basic_publish($message, 'app.hello');
        $this->channel->wait_for_pending_acks();
    }
}
