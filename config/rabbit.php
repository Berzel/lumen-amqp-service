<?php

use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

return [
    'exchanges' => [
        [
            'name' => 'app.hello',
            'type' => AMQPExchangeType::FANOUT
        ],
        [
            'name' => 'app.emails',
            'type' => AMQPExchangeType::DIRECT
        ]
    ],


    'queues' => [
        [
            'name' => 'hello',
            'arguments' => [
                'x-ha-policy' => 'nodes',
                'x-ha-policy-params' => ['rabbit@master', 'rabbit@slave']
            ]
        ],
        [
            'name' => 'emails',
            'arguments' => [
                'x-ha-policy' => 'all'
            ]
        ]
    ],


    'bindings' => [
        [
            'queue' => 'hello',
            'exchange' => 'app.hello'
        ],
        [
            'queue' => 'emails',
            'exchange' => 'app.emails'
        ]
    ],


    'consumes' => [
        [
            'queue' => 'hello',
            'tag' => 'hello-consumer',
            'callback' => function (AMQPMessage $msg) {
                $subChannel = app('sub-channel');
                $txt = json_decode($msg->getBody())->message;
                echo 'Consuming: ' . $txt . PHP_EOL;

                $body = json_encode(['message' => 'Hello, user!', 'to' => 'berzelbtumbude@gmail.com']);
                $message = new AMQPMessage($body, [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]);

                app('amqp')->publish($message, 'app.emails');
                $subChannel->basic_ack($msg->getDeliveryTag());
            }
        ],
        [
            'queue' => 'emails',
            'tag' => 'email-consumer',
            'callback' => function (AMQPMessage $msg) {
                $subChannel = app('sub-channel');
                $txt = json_decode($msg->getBody())->to;
                echo 'Sending email to...: ' . $txt . PHP_EOL;
                $subChannel->basic_ack($msg->getDeliveryTag());
            }
        ]
    ]
];
