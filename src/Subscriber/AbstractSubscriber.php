<?php

namespace Retrinko\CottonTail\Subscriber;

use PhpAmqpLib\Exception\AMQPProtocolConnectionException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Retrinko\CottonTail\Message\MessageFactory;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\RabbitMQ\Connector;
use Retrinko\Serializer\Serializers\JsonSerializer;
use Retrinko\Serializer\Traits\SerializerAwareTrait;

abstract class AbstractSubscriber
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    /**
     * @var Connector
     */
    protected $connector;
    /**
     * @var string
     */
    protected $queue;
    /**
     * @var MessageInterface
     */
    protected $currentReceivedMessage;
    /**
     * @var int
     */
    protected $numberOfMessagesToConsume = 0;
    /**
     * @var int
     */
    protected $numberOfReceivedMessages = 0;
    /**
     * @var bool
     */
    protected $requeueMessagesOnCallbackFails = false;

    /**
     * @param string $server
     * @param int $port
     * @param string $user
     * @param string $pass
     * @param string $queue
     * @param string $vhost
     * @param array $sslOptions
     */
    public function __construct($server, $port, $user, $pass, $queue, $vhost = '/', $sslOptions = [])
    {
        $this->queue = $queue;
        $this->logger = new NullLogger();
        $this->serializer = new JsonSerializer();
        $this->connector = new Connector($server, $port, $user, $pass, $vhost, $sslOptions);
    }

    /**
     * @param bool $requeue
     *
     * @return $this
     */
    public function requeueMessagesOnCallbackFails($requeue = false)
    {
        $this->requeueMessagesOnCallbackFails = $requeue;

        return $this;
    }

    /**
     * Method for processing $this->currentReceivedMessage
     * @return bool
     */
    abstract protected function callback();

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->connector->setLogger($logger);
    }

    /**
     * @param int $numberOfMessagesToConsume (0 => no limit)
     *
     * @return AbstractSubscriber
     */
    public function setNumberOfMessagesToConsume($numberOfMessagesToConsume = 0)
    {
        $this->numberOfMessagesToConsume = $numberOfMessagesToConsume;

        return $this;
    }

    /**
     * Trick for calling protected method onMessage as callback.
     * @return \Closure
     */
    protected function getCallback()
    {
        $server = $this;
        $callback = function ($amqpMessage) use ($server)
        {
            $server->onMessage($amqpMessage);
        };

        return $callback;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->numberOfReceivedMessages = 0;
        try
        {
            // Connect
            $this->connector->connect();
            // Start consuming
            $this->connector->basicConsume($this->queue, $this->getCallback());
            while (0 < count($this->connector->getChannelCallbacks()))
            {
                $this->connector->wait();
            }
            // Close connection
            $this->connector->closeConnection();
        }
        catch (AMQPProtocolConnectionException $e)
        {
            $this->logger->warning($e->getMessage());
            // Try reconnection
            $this->connector->connect(true);
        }
        catch (AMQPRuntimeException $e)
        {
            $this->logger->warning($e->getMessage());
            // Try reconnection
            $this->connector->connect(true);
        }
        catch (\ErrorException $e)
        {
            $this->logger->warning($e->getMessage());
            // Try reconnection
            $this->connector->connect(true);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param AMQPMessage $amqpMessage
     */
    final protected function onMessage(AMQPMessage $amqpMessage)
    {
        try
        {
            $this->numberOfReceivedMessages++;
            $this->currentReceivedMessage = MessageFactory::byAMQPMessage($amqpMessage);

            $this->logger->debug(sprintf('Processing message %s of %s...',
                                         $this->numberOfReceivedMessages,
                                         (0 == $this->numberOfMessagesToConsume)
                                             ? 'unlimited'
                                             : $this->numberOfMessagesToConsume));
            // Process message
            $this->callback();
            // Send ACK
            $this->connector->basicAck($amqpMessage);

        }
        catch (\Exception $e)
        {
            $this->logger->error('Exception processing message!',
                                 ['exception' => [
                                     'code' => $e->getCode(),
                                     'message' => $e->getMessage(),
                                     'file' => $e->getFile(),
                                     'line' => $e->getLine()]]);
            // Reject and requeue message if needed
            $this->connector->basicReject($amqpMessage, $this->requeueMessagesOnCallbackFails);
        }

        // Cancel consumption when limit reached ($this->numberOfMessagesToConsume)
        if (0 < $this->numberOfMessagesToConsume
            && $this->numberOfMessagesToConsume <= $this->numberOfReceivedMessages)
        {
            $this->logger->info(sprintf('Consumption limit reached! (limit: %s)',
                                        $this->numberOfMessagesToConsume));
            $this->connector->basicCancel($amqpMessage);
        }
    }

}