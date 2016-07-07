<?php


namespace Retrinko\CottonTail\Exceptions;


class ExecutionException extends Exception
{
    const EXCEPTION_CODE = 1000;
    
    const CODE_EXECUTION_ERROR = 1;

    /**
     * @param string $error
     *
     * @return static
     */
    public static function executionError($error)
    {
        return new static(sprintf('Execution error! %s', $error),
                          static::EXCEPTION_CODE + static::CODE_EXECUTION_ERROR);
    }
}