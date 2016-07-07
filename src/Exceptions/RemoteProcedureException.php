<?php


namespace Retrinko\CottonTail\Exceptions;


class RemoteProcedureException extends Exception
{
    const EXCEPTION_CODE = 5000;

    const CODE_PROCEDURE_NOT_FOUND = 1;

    /**
     * @param string $procedure
     *
     * @return static
     */
    public static function procedureNotFound($procedure)
    {
        return new static(sprintf('Procedure "%s" not found!', $procedure),
                          static::EXCEPTION_CODE + static::CODE_PROCEDURE_NOT_FOUND);
    }
}