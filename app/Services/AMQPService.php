<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPService
{
    private AMQPSocketConnection $connection;

    public static $count = 0;

    public function __construct()
    {
        $this->connect();
    }

    public function channel($id = null)
    {
        return $this->connection->channel($id);
    }

    /**
     * Connect to an amqp server
     * 
     * @return 
     */
    public function connect()
    {
        $host = env('AMQP_HOST', 'localhost');
        $port = env('AMQP_PORT', 5672);
        $user = env('AMQP_USERNAME', 'guest');
        $password = env('AMQP_PASSWORD', 'guest');
        $vhost = env('AMQP_VHOST', '/');

        $this->connection = new AMQPSocketConnection($host, $port, $user, $password, $vhost);

        return $this->connection;
    }

    public function boot()
    {
        $pubChannel = app('pub-channel');

        $exchanges = config('rabbit.exchanges');
        foreach ($exchanges as $key => $exchange) {
            $pubChannel->exchange_declare($exchange['name'], $exchange['type'], false, true, false);
        }

        $queues = config('rabbit.queues');
        foreach ($queues as $key => $queue) {
            $pubChannel->queue_declare($queue['name'], false, true, false, false);
        }

        $bindings = config('rabbit.bindings');
        foreach ($bindings as $key => $binding) {
            $pubChannel->queue_bind($binding['queue'], $binding['exchange']);
        }

        $ackCallback = function (AMQPMessage $msg) {
            # Code...
        };

        $nackCallback = function (AMQPMessage $msg) {
            $this->publish($msg, $msg->delivery_info['delivery_exchange']);
        };

        $pubChannel->confirm_select(false);
        $pubChannel->set_ack_handler($ackCallback);
        $pubChannel->set_nack_handler($nackCallback);
    }

    public function publish(AMQPMessage $message, $exchange)
    {
        self::$count++;
        $pubChannel = app('pub-channel');
        $message->delivery_info['delivery_tag'] = self::$count;
        $message->delivery_info['delivery_exchange'] = $exchange;
        $pubChannel->basic_publish($message, $exchange);
        $pubChannel->wait_for_pending_acks();
    }
}
