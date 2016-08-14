<?php


namespace Retrinko\CottonTail\Message\Payloads;


use Retrinko\CottonTail\Exceptions\PayloadException;

class RpcResponsePayload extends DefaultPayload
{
    const STATUS_CODE_SUCCESS = 200;
    const STATUS_CODE_ERROR   = 500;

    const KEY_STATUS   = 'status';
    const KEY_RESPONSE = 'response';
    const KEY_ERRORS   = 'errors';

    /**
     * @var array
     */
    protected $requiredFields = [self::KEY_STATUS,
                                 self::KEY_RESPONSE,
                                 self::KEY_ERRORS];

    /**
     * @param mixed $response
     * @param int $status
     * @param array $errors
     *
     * @return RpcResponsePayload
     */
    public static function create($response = '', $status = self::STATUS_CODE_SUCCESS, array $errors = [])
    {
        if (count($errors) > 0 && $status < self::STATUS_CODE_ERROR)
        {
            $status = self::STATUS_CODE_ERROR;
        }
        $data = [self::KEY_STATUS => $status,
                 self::KEY_RESPONSE => $response,
                 self::KEY_ERRORS => $errors];

        return new self($data);
    }

    /**
     * @return bool
     */
    public function isOK()
    {
        return self::STATUS_CODE_SUCCESS == $this->getStatus();
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->data[self::KEY_STATUS];
    }

    /**
     * @param array $errors
     *
     * @return $this
     */
    public function setErrors($errors = [])
    {
        $this->data[self::KEY_ERRORS] = $errors;
        if (count($errors) > 0)
        {
            $this->setStatus(self::STATUS_CODE_ERROR);
        }

        return $this;
    }

    /**
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatus($statusCode = self::STATUS_CODE_SUCCESS)
    {
        $this->data[self::KEY_STATUS] = $statusCode;

        return $this;
    }

    /**
     * @param string $errorMessage
     *
     * @return $this
     */
    public function addError($errorMessage)
    {
        $this->data[self::KEY_ERRORS][] = $errorMessage;
        $this->setStatus(self::STATUS_CODE_ERROR);

        return $this;
    }

    /**
     * @return int
     */
    public function hasErrors()
    {
        return count($this->getErrors());
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->data[self::KEY_ERRORS];
    }

    /**
     * @return string|array
     */
    public function getResponse()
    {
        return $this->data[self::KEY_RESPONSE];
    }

    /**
     * @param string|array $response
     *
     * @return $this
     * @throws PayloadException
     */
    public function setResponse($response = '')
    {
        if (false == is_string($response) && false == is_array($response))
        {
            throw PayloadException::badResponsePayload();
        }
        $this->data[self::KEY_RESPONSE] = $response;

        return $this;
    }
}