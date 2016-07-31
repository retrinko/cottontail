<?php


namespace Retrinko\CottonTail\Message\Messages;


use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Payloads\RpcResponsePayload;

class RpcResponseMessage extends BasicMessage
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
    public function __construct($body = '', array $properties = [])
    {
        // Override MessageInterface::PROPERTY_TYPE
        $properties[MessageInterface::PROPERTY_TYPE] = MessageInterface::TYPE_RPC_RESPONSE;
        parent::__construct($body, $properties);
    }

    /**
     * @return RpcResponsePayload
     */
    public function getPayload()
    {
        $data = parent::getPayload();

        return new RpcResponsePayload($data);
    }

    /**
     * @param array $properties
     *
     * @throws MessageException
     */
    protected function checkRequiredPropertiesPresence(array $properties)
    {
        parent::checkRequiredPropertiesPresence($properties);
        if (MessageInterface::TYPE_RPC_RESPONSE != $properties[MessageInterface::PROPERTY_TYPE])
        {
            throw MessageException::wrongMessageType($this->getType(),
                                                     MessageInterface::TYPE_RPC_REQUEST);
        }
    }

}