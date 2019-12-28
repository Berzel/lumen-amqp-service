<?php

namespace App\Jobs;

use PhpAmqpLib\Message\AMQPMessage;

class SendToRabbit extends Job
{
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
    public function handle()
    {
        $this->channel->confirm_select();
        $this->channel->set_ack_handler($this->ackCallback);
        $this->channel->set_nack_handler($this->nackCallback);

        $body = json_encode(['message' => 'Hello, world!']);
        $message = new AMQPMessage($body, [
            'content_type' => 'application/json'
        ]);

        $this->channel->basic_publish($message, 'app.hello');
        $this->channel->wait_for_pending_acks();
    }

    public function ackCallback(AMQPMessage $msg)
    {
        $msg = json_decode($msg->getBody())->message;
        fprintf(STDOUT, "<--Acknowledged-->\n");
    }

    function nackCallback(AMQPMessage $msg)
    {
        $msg = json_decode($msg->getBody())->message;
        fprintf(STDOUT, "<--Not Acknowledged-->\n");
    }
}
