<?php


namespace Retrinko\CottonTail\Message\Messages;


use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Payloads\RpcRequestPayload;

class RpcRequestMessage extends BasicMessage
{
    /**
     * @var array
     */
    protected $requiredProperties = [MessageInterface::PROPERTY_TYPE,
                                     MessageInterface::PROPERTY_CORRELATION_ID,
                                     MessageInterface::PROPERTY_CONTENT_TYPE];

    /**
     * @param string $body
     * @param array $properties
     */
    public function __construct($body = '', array $properties)
    {
        // Override MessageInterface::PROPERTY_TYPE
        $properties[MessageInterface::PROPERTY_TYPE] = MessageInterface::TYPE_RPC_REQUEST;
        // Generate a correlation id if not provided
        if (!isset($properties[MessageInterface::PROPERTY_CORRELATION_ID]))
        {
            $properties[MessageInterface::PROPERTY_CORRELATION_ID] = $this->generateCorrelationId();
        }
        $this->checkRequiredPropertiesPresence($properties);
        parent::__construct($body, $properties);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    protected function generateCorrelationId($prefix = '')
    {
        return ('' == trim($prefix))
            ? uniqid()
            : trim($prefix) . '.' . uniqid();
    }

    /**
     * @return RpcRequestPayload
     */
    public function getPayload()
    {
        $data = parent::getPayload();

        return new RpcRequestPayload($data);
    }

    /**
     * @param array $properties
     *
     * @throws MessageException
     */
    protected function checkRequiredPropertiesPresence(array $properties)
    {
        parent::checkRequiredPropertiesPresence($properties);
        if (MessageInterface::TYPE_RPC_REQUEST != $properties[MessageInterface::PROPERTY_TYPE])
        {
            throw MessageException::wrongMessageType($this->getType(),
                                                     MessageInterface::TYPE_RPC_REQUEST);
        }
    }


}