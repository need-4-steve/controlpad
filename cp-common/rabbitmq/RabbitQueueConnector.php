<?php

namespace CPCommon\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Queue\Connectors\ConnectorInterface;

class RabbitQueueConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new RabbitQueue($config);
    }
}
