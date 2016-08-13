<?php

namespace Retrinko\CottonTail\Tests\Unit\Message\Adaptors;

use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Message\Adaptors\PhpAmqpLibMessageAdaptor;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;

class PhpAmqpLibMessageAdaptorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Retrinko\CottonTail\Exceptions\MessageException
     */
    public function test_toMessageInterface_withBadParam_throwsMessageException()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();
        $message = new \ArrayObject([]);
        /** @noinspection PhpParamsInspection */
        $adaptor->toMessageInterface($message);
    }

    public function test_toMessageInterface_withDefaultAMQMessage_returnsBasicMessage()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();

        // Message with no type
        $amqpMessage = new AMQPMessage();
        $message = $adaptor->toMessageInterface($amqpMessage);
        $this->assertTrue($message instanceof BasicMessage,
                          'Returned value is not an instance of BasicMessage');
    }

    public function test_toMessageInterface_withUnknownTypeAMQMessage_returnsBasicMessage()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();

        // Message with "UNKNOWN" type
        $amqpMessage = new AMQPMessage();
        $amqpMessage->set(MessageInterface::PROPERTY_TYPE, 'UNKNOWN');
        $message = $adaptor->toMessageInterface($amqpMessage);
        $this->assertTrue($message instanceof BasicMessage,
                          'Returned value is not an instance of BasicMessage');
    }

    public function test_toMessageInterface_withBasicTypeAMQMessage_returnsBasicMessage()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();

        $amqpMessage = new AMQPMessage();
        $amqpMessage->set(MessageInterface::PROPERTY_TYPE, MessageInterface::TYPE_BASIC);
        $message = $adaptor->toMessageInterface($amqpMessage);
        $this->assertTrue($message instanceof BasicMessage,
                          'Returned value is not an instance of BasicMessage');
    }

    /**
     * @expectedException \Retrinko\CottonTail\Exceptions\MessageException
     */
    public function test_toMessageInterface_withBadRcpResponseAMQPMessage_throwsMessageException()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();

        // RPC response message
        $amqpMessage = new AMQPMessage();
        $amqpMessage->set(MessageInterface::PROPERTY_TYPE, MessageInterface::TYPE_RPC_RESPONSE);
        $adaptor->toMessageInterface($amqpMessage);
    }

    public function test_toMessageInterface_withProperRpcResponseTypeAMQMessage_returnsRpcResponseMessage()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();

        $amqpMessage = new AMQPMessage();
        $amqpMessage->set(MessageInterface::PROPERTY_TYPE, MessageInterface::TYPE_RPC_RESPONSE);
        $amqpMessage->set(MessageInterface::PROPERTY_CORRELATION_ID, uniqid());
        $amqpMessage->set(MessageInterface::PROPERTY_CONTENT_TYPE, MessageInterface::CONTENT_TYPE_PLAIN_TEXT);
        $message = $adaptor->toMessageInterface($amqpMessage);
        $this->assertTrue($message instanceof RpcResponseMessage,
                          'Returned value is not an instance of RpcResponseMessage');
    }

    /**
     * @expectedException \Retrinko\CottonTail\Exceptions\MessageException
     */
    public function test_toMessageInterface_withBadRcpRquestAMQPMessage_throwsMessageException()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();

        // RPC response message
        $amqpMessage = new AMQPMessage();
        $amqpMessage->set(MessageInterface::PROPERTY_TYPE, MessageInterface::TYPE_RPC_REQUEST);
        $adaptor->toMessageInterface($amqpMessage);
    }

    public function test_toMessageInterface_withProperRpcRequestTypeAMQMessage_returnsRpcRequestMessage()
    {
        $adaptor = new PhpAmqpLibMessageAdaptor();

        $amqpMessage = new AMQPMessage();
        $amqpMessage->set(MessageInterface::PROPERTY_TYPE, MessageInterface::TYPE_RPC_REQUEST);
        $amqpMessage->set(MessageInterface::PROPERTY_CONTENT_TYPE, MessageInterface::CONTENT_TYPE_PLAIN_TEXT);
        $message = $adaptor->toMessageInterface($amqpMessage);
        $this->assertTrue($message instanceof RpcRequestMessage,
                          'Returned value is not an instance of RpcRequestMessage');
    }

}