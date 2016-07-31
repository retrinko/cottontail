<?php


namespace Retrinko\CottonTail\Message;


use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;

class MessagesBuilder
{
    /**
     * @param string $contentType
     *
     * @return BasicMessage
     */
    public static function emptyBasicMessage($contentType)
    {
        return new BasicMessage('', [MessageInterface::PROPERTY_CONTENT_TYPE => $contentType]);
    }

    /**
     * @param string $contentType
     * @param $correlationId
     *
     * @return RpcResponseMessage
     */
    public static function emptyRpcResponse($contentType, $correlationId)
    {
        return new RpcResponseMessage('', [MessageInterface::PROPERTY_CONTENT_TYPE => $contentType,
                                           MessageInterface::PROPERTY_CORRELATION_ID => $correlationId]);

    }

    /**
     * @param string $contentType
     * @param string $correlationId
     *
     * @return RpcRequestMessage
     */
    public static function emptyRpcRequest($contentType, $correlationId = '')
    {
        $properties = [];
        $properties[MessageInterface::PROPERTY_CONTENT_TYPE] = $contentType;
        if (!empty($correlationId))
        {
            $properties[MessageInterface::PROPERTY_CORRELATION_ID] = $correlationId;
        }

        return new RpcRequestMessage('', $properties);
    }
}