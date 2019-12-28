<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class Amqp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to RabbitMQ and start consuming messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subChannel = app('sub-channel');

        $subChannel->exchange_declare('app.hello', AMQPExchangeType::FANOUT, false, true, false);
        $subChannel->queue_declare('hello', false, true, false, false);
        $subChannel->queue_bind('hello', 'app.hello');

        $callback = function (AMQPMessage $msg) use ($subChannel) {
            $txt = json_decode($msg->getBody())->message;
            echo 'Consuming: ' . $txt . PHP_EOL;
            usleep(2000000);
            $subChannel->basic_ack($msg->getDeliveryTag());
        };

        $subChannel->basic_consume('hello', 'hello-consumer', false, false, false, false, $callback);

        while ($subChannel->is_consuming()) {
            $subChannel->wait();
        }
    }
}
