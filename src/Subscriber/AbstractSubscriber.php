<?php

namespace Retrinko\CottonTail\Subscriber;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Retrinko\CottonTail\Connectors\ConnectorInterface;
use Retrinko\CottonTail\Exceptions\ConnectorException;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\Serializer\Serializers\JsonSerializer;
use Retrinko\Serializer\Traits\SerializerAwareTrait;

abstract class AbstractSubscriber
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    /**
     * @var ConnectorInterface
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
     * @param ConnectorInterface $connector
     * @param string $queue
     */
    public function __construct(ConnectorInterface $connector, $queue)
    {
        $this->queue = $queue;
        $this->logger = new NullLogger();
        $this->serializer = new JsonSerializer();
        $this->connector = $connector;
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
        $callback = function ($receivedMessage) use ($server)
        {
            $server->onMessage($this->connector->getMesageAdaptor()->toMessageInterface($receivedMessage));
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
        catch (ConnectorException $e)
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
     * @param MessageInterface $receivedMessage
     */
    final protected function onMessage(MessageInterface $receivedMessage)
    {
        try
        {
            $this->numberOfReceivedMessages++;
            $this->currentReceivedMessage = $receivedMessage;

            $this->logger->debug(sprintf('Processing message %s of %s...',
                                         $this->numberOfReceivedMessages,
                                         (0 == $this->numberOfMessagesToConsume)
                                             ? 'unlimited'
                                             : $this->numberOfMessagesToConsume));
            // Process message
            $this->callback();
            // Send ACK
            $this->connector->basicAck($receivedMessage);

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
            $this->connector->basicReject($receivedMessage, $this->requeueMessagesOnCallbackFails);
        }

        // Cancel consumption when limit reached ($this->numberOfMessagesToConsume)
        if (0 < $this->numberOfMessagesToConsume
            && $this->numberOfMessagesToConsume <= $this->numberOfReceivedMessages)
        {
            $this->logger->info(sprintf('Consumption limit reached! (limit: %s)',
                                        $this->numberOfMessagesToConsume));
            $this->connector->basicCancel($receivedMessage);
        }
    }

}