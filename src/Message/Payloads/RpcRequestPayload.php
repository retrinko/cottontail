<?php


namespace Retrinko\CottonTail\Message\Payloads;

use Retrinko\CottonTail\Exceptions\PayloadException;

class RpcRequestPayload extends DefaultPayload
{
    const KEY_PARAMS    = 'params';
    const KEY_PROCEDURE = 'procedure';

    /**
     * @var array
     */
    protected $requiredFields = [self::KEY_PROCEDURE, self::KEY_PARAMS];

    /**
     * @param string $procedure
     * @param array $params
     *
     * @return RpcRequestPayload
     * @throws PayloadException
     */
    public static function create($procedure, $params = [])
    {
        if (empty($procedure))
        {
            throw PayloadException::emptyProcedure();
        }
        $data = [self::KEY_PROCEDURE => $procedure,
                 self::KEY_PARAMS => $params];

        return new self($data);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return is_array($this->data) && array_key_exists(self::KEY_PARAMS, $this->data)
            ? $this->data[self::KEY_PARAMS] : [];
    }

    /**
     * @return string
     */
    public function getProcedure()
    {
        return is_array($this->data) && array_key_exists(self::KEY_PROCEDURE ,$this->data)
            ? $this->data[self::KEY_PROCEDURE] : '';
    }

}