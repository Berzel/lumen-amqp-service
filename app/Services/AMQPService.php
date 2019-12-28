<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPService
{
    private AMQPSocketConnection $connection;

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
    }
}
