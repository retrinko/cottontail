<?php


namespace Retrinko\CottonTail\Exceptions;


class SerializerRegistryException extends Exception
{
    const EXCEPTION_CODE = 7000;

    const CODE_SERILIZER_NOT_FOUND = 1;

    /**
     * @param string $contentType
     *
     * @return static
     */
    public static function notFound($contentType)
    {
        return new static(sprintf('Serializer for contentType "%s" not found!', $contentType),
                          static::EXCEPTION_CODE + static::CODE_SERILIZER_NOT_FOUND);
    }
}