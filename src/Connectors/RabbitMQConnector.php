<?php

namespace Retrinko\CottonTail\Connectors;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Retrinko\CottonTail\Exceptions\ConnectorException;
use Retrinko\CottonTail\Message\Adaptors\RabbitMQMessageAdaptor;
use Retrinko\CottonTail\Message\MessageInterface;

class RabbitMQConnector implements ConnectorInterface
{
    use LoggerAwareTrait;

    /**
     * @var RabbitMQMessageAdaptor
     */
    protected $messageAdaptor;
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
     * @var AMQPStreamConnection|AMQPSSLConnection
     */
    protected $connection;
    /**
     * @var AMQPChannel
     */
    protected $channel;
    /**
     * @var array
     */
    protected $sslOptions;

    /**
     * @param string $server
     * @param int $port
     * @param string $user
     * @param string $pass
     * @param string $vhost
     * @param array $sslOptions
     */
    public function __construct($server, $port, $user, $pass, $vhost = '/', $sslOptions = [])
    {
        $this->messageAdaptor = new RabbitMQMessageAdaptor();
        $this->logger = new NullLogger();
        $this->server = $server;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->vhost = $vhost;
        $this->sslOptions = $sslOptions;
    }

    /**
     * @return RabbitMQMessageAdaptor
     */
    public function getMesageAdaptor()
    {
        return $this->messageAdaptor;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * @return void
     */
    public function closeConnection()
    {
        $this->closeChannel();
        if ($this->connection instanceof AbstractConnection)
        {
            $this->connection->close();
            $this->logger->info('Connection clossed!', ['server' => $this->server,
                                                        'port' => $this->port,
                                                        'vhost' => $this->vhost]);
        }
        $this->connection = null;
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
     * @param bool $forceReconnection
     *
     * @return void
     */
    public function connect($forceReconnection = false)
    {
        $useSslConnection = !empty($this->sslOptions);
        $env = ['server' => $this->server, 'port' => $this->port, 'vhost' => $this->vhost,
                'ssl' => $useSslConnection];
        if (true == $forceReconnection
            || false == ($this->connection instanceof AMQPStreamConnection)
        )
        {
            $this->logger->debug('Stablishing connection...', $env);
            if ($useSslConnection)
            {
                $this->connection = new AMQPSSLConnection($this->server,
                                                          $this->port,
                                                          $this->user,
                                                          $this->pass,
                                                          $this->vhost,
                                                          $this->sslOptions);
            }
            else
            {
                $this->connection = new AMQPStreamConnection($this->server,
                                                             $this->port,
                                                             $this->user,
                                                             $this->pass,
                                                             $this->vhost);
            }
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
     * @param MessageInterface $message
     * @param string $exchangeName Destination xchange name.
     * @param string $routingKeyOrQueueName Routing key (if xchange is set) or the destination
     *     queue name.
     *
     * @return void
     */
    public function basicPublish(MessageInterface $message,
                                 $exchangeName = '',
                                 $routingKeyOrQueueName = '')
    {
        /** @var AMQPMessage $amqpMessage */
        $amqpMessage = $this->messageAdaptor->fromMessageInterface($message);
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
     *
     * @throws ConnectorException
     * @throws \Exception
     */
    public function basicConsume($queueName, $callback)
    {
        $this->logger->info(sprintf('Comsuming queue "%s"...', $queueName));
        try
        {
            $this->channel->basic_consume($queueName, '', false, false, false, false, $callback);
        }
        catch (\Exception $e)
        {
            if ($e instanceof AMQPExceptionInterface)
            {
                throw ConnectorException::internalException($e);
            }
            else
            {
                throw $e;
            }
        }
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
     * @param MessageInterface $message
     *
     * @throws ConnectorException
     */
    public function basicAck(MessageInterface $message)
    {
        /** @var AMQPMessage $amqpMessage */
        $amqpMessage = $message->getOriginalMessage();
        if (!$amqpMessage instanceof AMQPMessage)
        {
            throw ConnectorException::invalidOriginalMessageType(AMQPMessage::class,
                                                                 get_class($message));
        }
        $amqpMessage->delivery_info['channel']->basic_ack($amqpMessage->delivery_info['delivery_tag']);
        $this->logger->notice('ACK sent!', ['body' => $amqpMessage->body,
                                            'properties' => $amqpMessage->get_properties()]);
    }

    /**
     * @param MessageInterface $message
     * @param bool $requeueMessage
     *
     * @throws ConnectorException
     */
    public function basicReject(MessageInterface $message, $requeueMessage = false)
    {
        /** @var AMQPMessage $amqpMessage */
        $amqpMessage = $message->getOriginalMessage();
        if (!$amqpMessage instanceof AMQPMessage)
        {
            throw ConnectorException::invalidOriginalMessageType(AMQPMessage::class,
                                                                 get_class($message));
        }
        $amqpMessage->delivery_info['channel']
            ->basic_reject($amqpMessage->delivery_info['delivery_tag'], $requeueMessage);
        $this->logger->notice('NACK sent!', ['body' => $amqpMessage->body,
                                             'properties' => $amqpMessage->get_properties()]);
    }

    /**
     * @param MessageInterface $message
     *
     * @throws ConnectorException
     */
    public function basicCancel(MessageInterface $message)
    {
        /** @var AMQPMessage $amqpMessage */
        $amqpMessage = $message->getOriginalMessage();
        if (!$amqpMessage instanceof AMQPMessage)
        {
            throw ConnectorException::invalidOriginalMessageType(AMQPMessage::class,
                                                                 get_class($message));
        }
        $this->channel->basic_cancel($amqpMessage->delivery_info['consumer_tag']);
        $this->logger->info(sprintf('Message consumption stopped!'));
    }

}