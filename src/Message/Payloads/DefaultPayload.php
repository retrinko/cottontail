<?php


namespace Retrinko\CottonTail\Message\Payloads;


use Retrinko\CottonTail\Exceptions\PayloadException;
use Retrinko\CottonTail\Message\PayloadInterface;
use Retrinko\Serializer\Interfaces\SerializerInterface;

class DefaultPayload implements PayloadInterface
{
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @var array
     */
    protected $requiredFields = [];

    /**
     * DefaultPayload constructor.
     *
     * @param string|array $data
     */
    public function __construct($data = '')
    {
        if (is_array($data))
        {
            $this->checkRequiredFieldsPresence($data);
        }
        $this->data = $data;
    }

    /**
     * @param array $data
     *
     * @throws PayloadException
     */
    protected function checkRequiredFieldsPresence($data)
    {
        foreach ($this->requiredFields as $requiredField)
        {
            if (false == array_key_exists($requiredField, $data))
            {
                throw PayloadException::requiredFieldMissing($requiredField);
            }
        }
    }
    
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return DefaultPayload
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param SerializerInterface $serializer
     *
     * @return string
     */
    public function serialize(SerializerInterface $serializer)
    {
        return $serializer->serialize($this->data);
    }



}