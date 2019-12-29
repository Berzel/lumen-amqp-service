<?php

namespace App\Console\Commands;

use App\Services\AMQPService;
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

        $consumes = config('rabbit.consumes');

        foreach ($consumes as $key => $consume) {
            $subChannel->basic_consume($consume['queue'], $consume['tag'], false, false, false, false, $consume['callback']);
        }

        while ($subChannel->is_consuming()) {
            $subChannel->wait();
        }
    }
}
