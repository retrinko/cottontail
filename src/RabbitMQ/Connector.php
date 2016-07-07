<?php

namespace Retrinko\CottonTail\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection as AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Connector
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $user;
    /**
     * @var string
     */
    protected $pass;
    /**
     * @var string
     */
    protected $server;
    /**
     * @var int
     */
    protected $port;
    /**
     * @var string
     */
    protected $vhost;
    /**
     * @var AMQPConnection
     */
    protected $connection;
    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @param string $server
     * @param int $port
     * @param string $user
     * @param string $pass
     * @param string $vhost
     */
    public function __construct($server, $port, $user, $pass, $vhost = '/')
    {
        $this->logger = new NullLogger();
        $this->server = $server;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->vhost = $vhost;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * @param bool $forceReconnection
     *
     * @return void
     */
    public function connect($forceReconnection = false)
    {
        $env = ['server'=>$this->server, 'port'=>$this->port, 'vhost'=>$this->vhost];
        if (true == $forceReconnection || false == ($this->connection instanceof AMQPConnection))
        {
            $this->logger->debug('Stablishing connection...', $env);
            $this->connection = new AMQPConnection($this->server, $this->port, $this->user,
                                                   $this->pass, $this->vhost);
            $this->logger->info('Connection stablished!', $env);
        }
        elseif (false == $this->connection->isConnected())
        {
            $this->logger->debug('Restablishing connection...', $env);
            $this->connection->reconnect();
            $this->logger->info('Connection restablished!', $env);
        }
        $this->openChannel($forceReconnection);
    }


    /**
     * @param bool $forceNewChannell
     *
     * @return void
     */
    protected function openChannel($forceNewChannell = false)
    {
        if (true == $forceNewChannell || false == ($this->channel instanceof AMQPChannel))
        {
            $this->channel = $this->connection->channel();
            $this->logger->info('New channel opened!', [$this->channel->getChannelId()]);
        }
    }

    /**
     * @return void
     */
    public function closeConnection()
    {
        $this->connection = null;
        $this->closeChannel();
        if ($this->connection instanceof AMQPConnection)
        {
            $this->connection->close();
            $this->logger->info('Connection clossed!', ['server'=>$this->server,
                                                        'port'=>$this->port,
                                                        'vhost'=>$this->vhost]);
        }
    }

    /**
     * @return void
     */
    protected function closeChannel()
    {
        $this->channel = null;
        if ($this->channel instanceof AMQPChannel)
        {
            $channelId = $this->channel->getChannelId();
            $this->channel->close();
            $this->logger->info('Channel clossed!', [$channelId]);
        }
    }

    /**
     * @param string $queueName
     *
     * @return string
     */
    public function declareQueue($queueName = '')
    {
        list($generatedQueue, ,) = $this->channel->queue_declare($queueName, false, false, true,
                                                                 false);
        $this->logger->info('Queue declared!', [$generatedQueue]);

        return $generatedQueue;
    }

    /**
     * @param AMQPMessage $amqpMessage
     * @param string $exchangeName Destination xchange name.
     * @param string $routingKeyOrQueueName Routing key (if xchange is set) or the destination
     *     queue name.
     *
     * @return void
     */
    public function basicPublish(AMQPMessage $amqpMessage, $exchangeName = '',
                                 $routingKeyOrQueueName = '')
    {
        $currentMessageProperties = $amqpMessage->get_properties();
        if (!isset($currentMessageProperties['timestamp']))
        {
            $amqpMessage->set('timestamp', time());
        }
        $this->channel->basic_publish($amqpMessage, $exchangeName, $routingKeyOrQueueName);
        $this->logger->notice('Message published!', ['body' => $amqpMessage->body,
                                                     'properties' => $amqpMessage->get_properties()]);
    }

    /**
     * @param string $queueName
     * @param callable $callback
     */
    public function basicConsume($queueName, $callback)
    {
        $this->logger->info(sprintf('Comsuming queue "%s"...', $queueName));
        $this->channel->basic_consume($queueName, '', false, false, false, false, $callback);
    }

    /**
     * @param int $timeOut Time in seconds (0 = infinite wait).
     */
    public function wait($timeOut = 0)
    {
        $this->logger->info(sprintf('Waiting (timeout: %ss)...', $timeOut));
        $this->channel->wait(null, false, $timeOut);
    }

    /**
     * @param int $prefechCount
     */
    public function defineQoS($prefechCount = 1)
    {
        $this->channel->basic_qos(null, $prefechCount, null);
        $this->logger->info(sprintf('QoS defined (prefetch count: %s)!', $prefechCount));
    }

    /**
     * @return array
     */
    public function getChannelCallbacks()
    {
        return $this->channel->callbacks;
    }

    /**
     * @param AMQPMessage $message
     */
    public function basicAck($message)
    {
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        $this->logger->notice('ACK sent!', ['body' => $message->body,
                                            'properties' => $message->get_properties()]);
    }

    /**
     * @param AMQPMessage $message
     * @param bool $requeueMessage
     */
    public function basicReject($message, $requeueMessage = false)
    {
        $message->delivery_info['channel']
            ->basic_reject($message->delivery_info['delivery_tag'], $requeueMessage);
        $this->logger->notice('NACK sent!', ['body' => $message->body,
                                             'properties' => $message->get_properties()]);
    }

    /**
     * @param AMQPMessage $message
     */
    public function basicCancel($message)
    {
        $this->channel->basic_cancel($message->delivery_info['consumer_tag']);
        $this->logger->info(sprintf('Message consumption stopped!'));
    }

}