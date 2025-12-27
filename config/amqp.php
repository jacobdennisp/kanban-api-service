<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AMQP Properties separated by key
    |--------------------------------------------------------------------------
    */

    'use' => 'production',

    'properties' => [

        'production' => [
            'host'                  => env('AMQP_HOST', 'localhost'),
            'port'                  => env('AMQP_PORT', 5672),
            'username'              => env('AMQP_USER', 'guest'),
            'password'              => env('AMQP_PASSWORD', 'guest'),
            'vhost'                 => env('AMQP_VHOST', '/'),
            'connect_options'       => [],
            'ssl_options'           => [],

            'exchange'              => env('AMQP_EXCHANGE', 'kanban_exchange'),
            'exchange_type'         => env('AMQP_EXCHANGE_TYPE', 'topic'),
            'exchange_passive'      => env('AMQP_EXCHANGE_PASSIVE', false),
            'exchange_durable'      => env('AMQP_EXCHANGE_DURABLE', true),
            'exchange_auto_delete'  => env('AMQP_EXCHANGE_AUTO_DELETE', false),
            'exchange_internal'     => env('AMQP_EXCHANGE_INTERNAL', false),
            'exchange_nowait'       => env('AMQP_EXCHANGE_NOWAIT', false),
            'exchange_properties'   => [],

            'queue'                 => env('AMQP_QUEUE', 'kanban_tasks'),
            'queue_force_declare'   => env('AMQP_QUEUE_FORCE_DECLARE', false),
            'queue_passive'         => env('AMQP_QUEUE_PASSIVE', false),
            'queue_durable'         => env('AMQP_QUEUE_DURABLE', true),
            'queue_exclusive'       => env('AMQP_QUEUE_EXCLUSIVE', false),
            'queue_auto_delete'     => env('AMQP_QUEUE_AUTO_DELETE', false),
            'queue_nowait'          => env('AMQP_QUEUE_NOWAIT', false),
            'queue_properties'      => ['x-ha-policy' => ['S', 'all']],

            'consumer_tag'          => env('AMQP_CONSUMER_TAG', ''),
            'consumer_no_local'     => env('AMQP_CONSUMER_NO_LOCAL', false),
            'consumer_no_ack'       => env('AMQP_CONSUMER_NO_ACK', false),
            'consumer_exclusive'    => env('AMQP_CONSUMER_EXCLUSIVE', false),
            'consumer_nowait'       => env('AMQP_CONSUMER_NOWAIT', false),
            'timeout'               => 0,
            'persistent'            => false,

            'qos'                   => false,
            'qos_prefetch_size'     => 0,
            'qos_prefetch_count'    => 1,
            'qos_a_global'          => false,
        ],

        'local' => [
            'host'                  => env('AMQP_HOST', 'localhost'),
            'port'                  => env('AMQP_PORT', 5672),
            'username'              => env('AMQP_USER', 'guest'),
            'password'              => env('AMQP_PASSWORD', 'guest'),
            'vhost'                 => env('AMQP_VHOST', '/'),
            'connect_options'       => [],
            'ssl_options'           => [],

            'exchange'              => env('AMQP_EXCHANGE', 'kanban_exchange'),
            'exchange_type'         => env('AMQP_EXCHANGE_TYPE', 'topic'),
            'exchange_passive'      => env('AMQP_EXCHANGE_PASSIVE', false),
            'exchange_durable'      => env('AMQP_EXCHANGE_DURABLE', true),
            'exchange_auto_delete'  => env('AMQP_EXCHANGE_AUTO_DELETE', false),
            'exchange_internal'     => env('AMQP_EXCHANGE_INTERNAL', false),
            'exchange_nowait'       => env('AMQP_EXCHANGE_NOWAIT', false),
            'exchange_properties'   => [],

            'queue'                 => env('AMQP_QUEUE', 'kanban_tasks'),
            'queue_force_declare'   => env('AMQP_QUEUE_FORCE_DECLARE', false),
            'queue_passive'         => env('AMQP_QUEUE_PASSIVE', false),
            'queue_durable'         => env('AMQP_QUEUE_DURABLE', true),
            'queue_exclusive'       => env('AMQP_QUEUE_EXCLUSIVE', false),
            'queue_auto_delete'     => env('AMQP_QUEUE_AUTO_DELETE', false),
            'queue_nowait'          => env('AMQP_QUEUE_NOWAIT', false),
            'queue_properties'      => ['x-ha-policy' => ['S', 'all']],

            'consumer_tag'          => env('AMQP_CONSUMER_TAG', ''),
            'consumer_no_local'     => env('AMQP_CONSUMER_NO_LOCAL', false),
            'consumer_no_ack'       => env('AMQP_CONSUMER_NO_ACK', false),
            'consumer_exclusive'    => env('AMQP_CONSUMER_EXCLUSIVE', false),
            'consumer_nowait'       => env('AMQP_CONSUMER_NOWAIT', false),
            'timeout'               => 0,
            'persistent'            => false,

            'qos'                   => false,
            'qos_prefetch_size'     => 0,
            'qos_prefetch_count'    => 1,
            'qos_a_global'          => false,
        ],

    ],

];