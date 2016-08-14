<?php


namespace Retrinko\CottonTail\Exceptions;


class PayloadException extends Exception
{
    const EXCEPTION_CODE = 3000;

    const CODE_REQUIRED_FIELD_MISSING = 1;
    const CODE_BAD_RESPONSE_PAYLOAD   = 2;
    const CODE_EMPTY_PROCEDURE        = 3;

    /**
     * @param string $requiredField
     *
     * @return static
     */
    public static function requiredFieldMissing($requiredField)
    {
        return new static(sprintf('Required payload field "%s" is missing!',
                                  $requiredField),
                          static::EXCEPTION_CODE + static::CODE_REQUIRED_FIELD_MISSING);
    }

    /**
     * @return static
     */
    public static function badResponsePayload()
    {
        return new static('Payload\'s response field must be an array or a string!',
                          static::EXCEPTION_CODE + static::CODE_BAD_RESPONSE_PAYLOAD);
    }

    /**
     * @return static
     */
    public static function emptyProcedure()
    {
        return new static('Payload\'s procedure can not be empty!',
                          static::EXCEPTION_CODE + static::CODE_BAD_RESPONSE_PAYLOAD);
    }

}