<?php
namespace Retrinko\CottonTail\Message\Adaptors;

use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;

interface MessageAdaptorInterface
{
    /**
     * @param mixed $message
     *
     * @return BasicMessage|RpcRequestMessage|RpcResponseMessage
     */
    public function toMessageInterface($message);

    /**
     * @param MessageInterface $message
     *
     * @return mixed
     */
    public function fromMessageInterface(MessageInterface $message);
}