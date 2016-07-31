<?php


namespace Retrinko\CottonTail\Exceptions;


class ConnectorException extends Exception
{
    const EXCEPTION_CODE = 6000;

    const CODE_INTERNAL_EXCEPTION            = 1;
    const CODE_INVALID_ORIGINAL_MESSAGE_TYPE = 2;

    /**
     * @param \Exception $e
     *
     * @return static
     */
    public static function internalException(\Exception $e)
    {
        return new static(sprintf('Internal connector exception! %s', $e->getMessage()),
                          static::EXCEPTION_CODE + static::CODE_INTERNAL_EXCEPTION,
                          $e);
    }

    /**
     * @param string $expected
     * @param string $received
     *
     * @return static
     */
    public static function invalidOriginalMessageType($expected, $received)
    {
        return new static(sprintf('Invalid original message type! Expected: %s, Received: %s',
                                  $expected, $received),
                          static::EXCEPTION_CODE + static::CODE_INVALID_ORIGINAL_MESSAGE_TYPE);
    }
}