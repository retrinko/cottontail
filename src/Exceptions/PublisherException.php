<?php


namespace Retrinko\CottonTail\Exceptions;


class PublisherException extends Exception
{
    const EXCEPTION_CODE = 4000;
    
    const CODE_NO_DESTINATION_SET = 1;

    /**
     * @return static
     */
    public static function noDestinationSet()
    {
        return new static('No destination set! Set a destination using method setDestination.',
                          static::EXCEPTION_CODE + static::CODE_NO_DESTINATION_SET);
    }
}