<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPService
{
    private AMQPSocketConnection $connection;
    private $pubChannel;
    private $subChannel;

    public static $count = 0;

    public function __construct()
    {
        $this->connect();
        $this->pubChannel = $this->connection->channel();
        $this->subChannel = $this->connection->channel();
    }

    public function getPubChannel()
    {
        return $this->pubChannel;
    }

    public function getSubChannel()
    {
        return $this->subChannel;
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
        $this->declareExchange();
        $this->declareQueue();
        $this->bindQueue();
        $this->confirmMode();
    }

    public function confirmMode()
    {
        $ackCallback = function (AMQPMessage $msg) {
            # Code...
        };

        // republish the message if it was nacked
        $nackCallback = function (AMQPMessage $msg) {
            $this->publish($msg, $msg->delivery_info['delivery_exchange']);
        };

        $this->pubChannel->confirm_select(false);
        $this->pubChannel->set_ack_handler($ackCallback);
        $this->pubChannel->set_nack_handler($nackCallback);
    }

    public function bindQueue()
    {
        $bindings = config('rabbit.bindings');
        foreach ($bindings as $key => $binding) {
            $this->pubChannel->queue_bind($binding['queue'], $binding['exchange']);
        }
    }

    public function declareQueue()
    {
        $queues = config('rabbit.queues');
        foreach ($queues as $key => $queue) {
            $this->pubChannel->queue_declare($queue['name'], false, true, false, false);
        }
    }

    public function declareExchange()
    {
        $exchanges = config('rabbit.exchanges');
        foreach ($exchanges as $key => $exchange) {
            $this->pubChannel->exchange_declare($exchange['name'], $exchange['type'], false, true, false);
        }
    }

    public function publish(AMQPMessage $message, $exchange)
    {
        self::$count++;
        $message->delivery_info['delivery_tag'] = self::$count;
        $message->delivery_info['delivery_exchange'] = $exchange;
        // Please node the basic publish methos of the original phpamqplib was modified to allow tracking messages using the message delivery tag
        $this->pubChannel->basic_publish($message, $exchange);
        $this->pubChannel->wait_for_pending_acks();
    }

    public function consume()
    {
        $consumes = config('rabbit.consumes');

        foreach ($consumes as $key => $consume) {
            $this->subChannel->basic_consume($consume['queue'], $consume['tag'], false, false, false, false, $consume['callback']);
        }

        while ($this->subChannel->is_consuming()) {
            $this->subChannel->wait();
        }
    }
}
