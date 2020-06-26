<?php

namespace CPCommon\RabbitMQ;

use Illuminate\Contracts\Queue\Queue;
use Illuminate\Container\Container;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use CPCommon\Events\EventJob;

class RabbitQueue implements Queue
{

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;
    protected $connectionName;
    protected $connection = null;
    protected $channel = null;
    protected $queueSize = 0;

    public function __construct(array $config)
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['username'],
                $config['password'],
                $config['vhost']
            );
            $this->channel = $this->connection->channel();
        } catch (\Exception $e) {
            app('log')->error($e);
        }

        // When would we use this? AMQPSSLConnection
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function size($queue = null)
    {
        return $this->queueSize;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string|object  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        try {
            if ($this->channel == null) {
                app('log')->error('Failed to queue job', ['job' => $job, 'data' => $data, 'queue' => $queue, 'reason' => 'channel not set']);
                return;
            }
            $exchange = 'router';
            /*
                name: $exchange
                type: direct
                passive: false
                durable: true // the exchange will survive server restarts
                auto_delete: false //the exchange won't be deleted once the channel is closed.
            */
            $this->channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

            if ($queue != null && $queue != '') {
                /*
                name: $queue
                passive: false
                durable: true // the queue will survive server restarts
                exclusive: false // the queue can be accessed in other channels
                auto_delete: false //the queue won't be deleted once the channel is closed.
                */
                $queueInfo = $this->channel->queue_declare($queue, false, true, false, false);
                $this->queueSize = is_array($queueInfo) ? $queueInfo[1] : 0;
                $this->channel->queue_bind($queue, $exchange);
            } else {
                // TODO what would the default queue be?
            }

            $body = json_encode($job->data[0]);
            $message = new AMQPMessage($body, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
            $this->channel->basic_publish($message, $exchange);
        } catch(\Exception $e) {
            app('log')->error($e, ['job' => $job, 'data' => $data, 'queue' => $queue]);
        }
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $queue
     * @param  string|object  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function pushOn($queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        // TODO not sure how this works
        app('log')->error('pushRaw not implemented', ['payload' => $payload, 'queue' => $queue, 'options' => $options]);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string|object  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        // TODO implement
        app('log')->error('later not implemented', ['delay' => $delay, 'job' => $job, 'data' => $data, 'queue' => $queue]);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  string  $queue
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string|object  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }

    /**
     * Push an array of jobs onto the queue.
     *
     * @param  array   $jobs
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
     public function bulk($jobs, $data = '', $queue = null)
     {
         foreach ((array) $jobs as $job) {
             $this->push($job, $data, $queue);
         }
     }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        try {
            if ($this->channel == null) {
                app('log')->error('Failed to pop job', ['queue' => $queue, 'reason' => 'channel not set']);
                return;
            }
            $exchange = 'router';
            /*
                name: $exchange
                type: direct
                passive: false
                durable: true // the exchange will survive server restarts
                auto_delete: false //the exchange won't be deleted once the channel is closed.
            */
            $this->channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

            if ($queue != null && $queue != '') {
                /*
                name: $queue
                passive: false
                durable: true // the queue will survive server restarts
                exclusive: false // the queue can be accessed in other channels
                auto_delete: false //the queue won't be deleted once the channel is closed.
                */
                $queueInfo = $this->channel->queue_declare($queue, false, true, false, false);
                $this->queueSize = is_array($queueInfo) ? $queueInfo[1] : 0;
                $this->channel->queue_bind($queue, $exchange);
            } else {
                // TODO what would the default queue be?
            }

            $message = $this->channel->basic_get($queue);
            if ($message == null) {
                return null;
            }
            if ($queue === 'events') {
                // $rawBody, $tries, $connectionName, $queue, $maxTries
                $job = new EventJob($message->body, 0, $this->getConnectionName(), $queue, 3);
                $this->channel->basic_ack($message->delivery_info['delivery_tag']);
                return $job;
            } else {
                $this->channel->basic_reject($message->delivery_info['delivery_tag'], true);
                app('log')->error('Rabbit pop: unsupported queue', ['queue' => $queue]);
                return null;
            }
        } catch (\Exception $e) {
            app('log')->error($e, ['queue' => $queue]);
            return null;
        }
    }

    /**
     * Get the connection name for the queue.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set the connection name for the queue.
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnectionName($name)
    {
        $this->connectionName = $name;
        return $this;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function __destruct()
    {
        if ($this->channel !== null) {
            $this->channel->close();
        }
        if ($this->connection !== null) {
            $this->connection->close();
        }
    }
}
