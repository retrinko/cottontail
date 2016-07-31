<?php

namespace Retrinko\CottonTail\Rpc;

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Retrinko\CottonTail\Connectors\ConnectorInterface;
use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\Payloads\RpcRequestPayload;
use Retrinko\CottonTail\Message\Payloads\RpcResponsePayload;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;
use Retrinko\Serializer\Serializers\JsonSerializer;
use Retrinko\Serializer\Traits\SerializerAwareTrait;

/**
 * Class Client
 * @package CottonTail\Rpc
 */
class Client
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
    protected $exchange;
    /**
     * @var string
     */
    protected $requestsQueue;
    /**
     * @var string
     */
    protected $forcedResponsesQueue;
    /**
     * @var string
     */
    protected $responsesQueue;
    /**
     * @var RpcResponseMessage
     */
    protected $rpcResponse;
    /**
     * @var string
     */
    protected $correlarionId;
    /**
     * Timeout in seconds
     * @var int
     */
    protected $timeOut = 15;

    /**
     * @param ConnectorInterface $connector
     * @param string $exchange
     */
    public function __construct(ConnectorInterface $connector, $exchange)
    {
        $this->logger = new NullLogger();
        $this->serializer = new JsonSerializer();
        $this->connector = $connector;
        $this->exchange = $exchange;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->connector->setLogger($logger);
    }

    /**
     * @param string $queue
     *
     * @return $this
     */
    public function forceResponsesOnExistingQueue($queue)
    {
        $this->forcedResponsesQueue = $queue;

        return $this;
    }

    /**
     * @param string $routingKey
     * @param string $procedure
     * @param array $params
     * @param bool $closeConnectionAfterCall
     *
     * @return RpcResponsePayload
     */
    public function call($routingKey, $procedure, array $params = [],
                         $closeConnectionAfterCall = false)
    {
        // Reset current response object
        $this->resetCurrentRpcResponse();

        // Connect
        $this->connector->connect();

        // Declare responses queue
        $this->declareResponsesQueue();

        // Compose request message
        $request = new RpcRequestMessage();
        $request->setContentType($this->getSerializer()->getSerializedContentType());
        $request->setCorrelationId($request->generateCorrelationId());
        $request->setExpiration(1000 * $this->getTimeOut());
        $request->setReplyTo($this->responsesQueue);
        $request->setPayload(RpcRequestPayload::create($procedure, $params));

        // Send request
        $this->sendRequest($request, $routingKey);
        if (true == $closeConnectionAfterCall)
        {
            $this->connector->closeConnection();
        }

        return $this->rpcResponse->getPayload();
    }

    /**
     * @return $this
     */
    protected function resetCurrentRpcResponse()
    {
        $this->rpcResponse = null;

        return $this;
    }

    /**
     * @return void
     */
    protected function declareResponsesQueue()
    {
        $this->responsesQueue = $this->forcedResponsesQueue;
        if (is_null($this->responsesQueue))
        {
            // Use server autogenerated queue
            $this->responsesQueue = $this->connector->declareQueue();
        }
    }

    /**
     * @return int seconds
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * @param int $timeOut seconds
     *
     * @return Client
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;

        return $this;
    }

    /**
     * @param RpcRequestMessage $request
     * @param string $routingKey
     */
    final protected function sendRequest(RpcRequestMessage $request, $routingKey)
    {
        $this->correlarionId = $request->getCorrelationId();

        // Publish request message
        $this->connector->basicPublish($request->toAMQPMessage(), $this->exchange, $routingKey);

        // Wait for response
        $this->logger->debug('Waiting for response...', ['body' => $request->getBody(),
                                                         'properties' => $request->getProperties()]);
        $this->connector->basicConsume($this->responsesQueue, $this->getCallback());
        while (!$this->rpcResponse)
        {
            $this->connector->wait($this->getTimeOut());
        }
    }

    /**
     * Trick for calling protected method onResponse as callback.
     * @return \Closure
     */
    protected function getCallback()
    {
        $client = $this;
        $callback = function ($amqpMessage) use ($client)
        {
            $client->onResponse($amqpMessage);
        };

        return $callback;
    }

    /**
     * @param AMQPMessage $response
     *
     * @throws MessageException
     */
    protected function onResponse(AMQPMessage $response)
    {
        $this->logger->notice('Message received!', ['body' => $response->body,
                                                    'properties' => $response->get_properties()]);
        try
        {
            $this->rpcResponse = RpcResponseMessage::loadAMQPMessage($response);
            if ($this->correlarionId != $this->rpcResponse->getCorrelationId())
            {
                throw MessageException::wrongCorrelationId($this->rpcResponse->getCorrelationId(),
                                                           $this->correlarionId);
            }
            // Send ack
            $this->connector->basicAck($response);
        }
        catch (\Exception $e)
        {
            $responsePayload = RpcResponsePayload::create()->addError($e->getMessage());
            $this->rpcResponse = new RpcResponseMessage();
            $this->rpcResponse->setPayload($responsePayload);
            $this->connector->basicReject($response, false);
        }

    }
}