<?php


namespace Retrinko\CottonTail\Rpc;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Retrinko\CottonTail\Connectors\ConnectorInterface;
use Retrinko\CottonTail\Exceptions\RpcExecutionException;
use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Exceptions\RemoteProcedureException;
use Retrinko\CottonTail\Message\MessagesBuilder;
use Retrinko\CottonTail\Message\Payloads\RpcResponsePayload;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;
use Retrinko\Serializer\Serializers\JsonSerializer;
use Retrinko\Serializer\Traits\SerializerAwareTrait;

/**
 * Class Server
 * @package CottonTail\Rpc
 */
class Server
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    static $proceduresClass;
    /**
     * @var ConnectorInterface
     */
    protected $connector;
    /**
     * @var string
     */
    protected $requestsQueue;
    /**
     * @var string
     */
    protected $correlarionId;
    /**
     * @var RpcResponseMessage
     */
    protected $currentResponse;
    /**
     * @var RpcRequestMessage
     */
    protected $currentRequest;

    /**
     * @param ConnectorInterface $connector
     * @param $requestsQueue
     */
    public function __construct(ConnectorInterface $connector, $requestsQueue)
    {
        $this->logger = new NullLogger();
        $this->serializer = new JsonSerializer();
        $this->connector = $connector;
        $this->requestsQueue = $requestsQueue;
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
     * @param mixed $proceduresClass
     *
     * @return Server
     */
    public function registerProceduresClass($proceduresClass)
    {
        self::$proceduresClass = $proceduresClass;

        return $this;
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->connector->connect();
        $this->connector->defineQoS(1);
        $this->connector->basicConsume($this->requestsQueue, $this->getCallback());
        while (count($this->connector->getChannelCallbacks()))
        {
            $this->logger->debug('Waiting...');
            $this->connector->wait();
        }
        $this->connector->closeConnection();
    }

    /**
     * Trick for calling protected method onRequest as callback.
     * @return \Closure
     */
    protected function getCallback()
    {
        $server = $this;
        $callback = function ($receivedMessage) use ($server)
        {
            $server->onRequest($this->connector->getMesageAdaptor()->toMessageInterface($receivedMessage));
        };

        return $callback;
    }

    /**
     * Strategy: preCallback() -> callback() -> postCallback()
     *
     * @param RpcRequestMessage $request
     */
    final protected function onRequest(RpcRequestMessage $request)
    {
        // Reset possible previous values
        $this->currentRequest = null;
        try
        {
            $this->logger->notice('Message received!', ['body' => $request->getBody(),
                                                        'properties' => $request->getProperties()]);
            $this->currentRequest = $request;

            // Create an empty response
            $this->currentResponse = MessagesBuilder::emptyRpcResponse($this->getSerializer()->getSerializedContentType(),
                                                                       $this->currentRequest->getCorrelationId());
            //  Execute callback strategy
            $this->preCallback();
            $this->callback();
            $this->postCallback();

            // Send response
            $this->sendResponse($this->currentResponse);

            // Send ACK for request
            $this->connector->basicAck($request);
        }
        catch (RemoteProcedureException $e)
        {
            $this->logger->warning($e->getMessage());
            $responsePayload = RpcResponsePayload::create()
                                                 ->addError('Bad request! ' . $e->getMessage());
            $this->currentResponse->setPayload($responsePayload);
            // Send response
            $this->sendResponse($this->currentResponse);
            // Reject and drop conflictive message
            $this->connector->basicReject($request);
        }
        catch (MessageException $e)
        {
            $this->logger->warning($e->getMessage());
            $responsePayload = RpcResponsePayload::create()
                                                 ->addError('Bad request! ' . $e->getMessage());
            $this->currentResponse->setPayload($responsePayload);
            // Send response
            $this->sendResponse($this->currentResponse);
            // Reject and drop conflictive message
            $this->connector->basicReject($request);
        }
        catch (RpcExecutionException $e)
        {
            $this->logger->warning($e->getMessage());
            $responsePayload = RpcResponsePayload::create()
                                                 ->addError('Execution error! ' . $e->getMessage());
            $this->currentResponse->setPayload($responsePayload);
            // Send response
            $this->sendResponse($this->currentResponse);
            // Reject and drop conflictive message
            $this->connector->basicReject($request);
        }
        catch (\Exception $e)
        {
            $this->logger->error(sprintf('Unexpected error! [File: %s, Line: %s]: %s ',
                                         $e->getFile(), $e->getLine(), $e->getMessage()));
            // Reject and drop conflictive message
            $this->connector->basicReject($request);
        }
    }

    /**
     * Allow access/modification to $this->currentRequest before callback
     * @return void
     */
    public function preCallback()
    {

    }

    /**
     * @throws RemoteProcedureException
     */
    protected function callback()
    {
        $procedure = $this->currentRequest->getPayload()->getProcedure();
        $params = $this->currentRequest->getPayload()->getParams();

        if (!is_callable([self::$proceduresClass, $procedure]))
        {
            throw RemoteProcedureException::procedureNotFound($procedure);
        }

        try
        {
            $executionResult = call_user_func_array([self::$proceduresClass, $procedure], $params);
            $payload = RpcResponsePayload::create($executionResult);
            $this->currentResponse->setPayload($payload);
        }
        catch (\Exception $e)
        {
            throw RpcExecutionException::executionError($e->getMessage());
        }
    }

    /**
     * Allow access/modification to $this->currentResponse after callback
     * @return void
     */
    public function postCallback()
    {

    }

    /**
     * @param RpcResponseMessage $response
     *
     * @return void
     */
    protected function sendResponse(RpcResponseMessage $response)
    {
        if ($this->currentRequest instanceof RpcRequestMessage)
        {
            $correlationId = $this->currentRequest->getCorrelationId();
            $response->setCorrelationId($correlationId);

            // Publish reponse message
            $this->connector->basicPublish($response, '', $this->currentRequest->getReplyTo());
        }
        else
        {
            $this->logger->warning('Imposible to send response! Invalid request.');
        }
    }
}