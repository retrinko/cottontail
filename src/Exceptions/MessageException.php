<?php


namespace Retrinko\CottonTail\Exceptions;


class MessageException extends Exception
{
    const EXCEPTION_CODE = 2000;

    const CODE_REQUIRED_PROPERTY_MISSING = 1;
    const CODE_WRONG_CORRELATION_ID      = 2;
    const CODE_WRONG_MESSAGE_TYPE        = 3;

    /**
     * @param string $requiredProperty
     *
     * @return static
     */
    public static function requiredPropertyMissing($requiredProperty)
    {
        return new static(sprintf('Required message property "%s" is missing!',
                                  $requiredProperty),
                          static::EXCEPTION_CODE + static::CODE_REQUIRED_PROPERTY_MISSING);
    }

    /**
     * @param string $receivedCorrelationId
     * @param string $expectedCorrelationId
     *
     * @return static
     */
    public static function wrongCorrelationId($receivedCorrelationId, $expectedCorrelationId)
    {
        return new static(sprintf('Invalid message! Wrong correlation id. ' .
                                  'Expected: %s, Received: %s.',
                                  $expectedCorrelationId,
                                  $receivedCorrelationId),
                          static::EXCEPTION_CODE + static::CODE_WRONG_CORRELATION_ID);
    }

    /**
     * @param string $received
     * @param string $expected
     *
     * @return static
     */
    public static function wrongMessageType($received, $expected)
    {
        return new static(sprintf('Wrong message type! Expected: %s, Received: %s.',
                                  $expected, $received),
                          static::EXCEPTION_CODE + static::CODE_WRONG_MESSAGE_TYPE);
    }

}