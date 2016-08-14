<?php

namespace Retrinko\CottonTail\Tests\Unit\Message;


use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;
use Retrinko\CottonTail\Message\MessagesBuilder;
use Retrinko\CottonTail\Message\Payloads\RpcResponsePayload;

class MessagesBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function test_emptyBasicMessage_buildProperBasicMessage()
    {
        $contentType = MessageInterface::CONTENT_TYPE_PLAIN_TEXT;
        $message = MessagesBuilder::emptyBasicMessage($contentType);
        $this->assertTrue($message instanceof BasicMessage);
        $this->assertEquals('', $message->getBody());
        $this->assertEquals($contentType, $message->getContentType());
    }


    public function test_emptyRpcRequest_buildProperRpcRequest()
    {
        $contentType = MessageInterface::CONTENT_TYPE_PLAIN_TEXT;
        $correlationId = 123;
        $message = MessagesBuilder::emptyRpcRequest($contentType, $correlationId);
        $this->assertTrue($message instanceof RpcRequestMessage);
        $this->assertEquals($correlationId, $message->getCorrelationId());
        $this->assertEquals($contentType, $message->getContentType());
        $this->assertEquals('', $message->getPayload()->getProcedure());
        $this->assertEquals([], $message->getPayload()->getParams());
    }

    public function test_emptyRpcRequest_withEmptyCorretlationId_generatesOne()
    {
        $contentType = MessageInterface::CONTENT_TYPE_PLAIN_TEXT;
        $message = MessagesBuilder::emptyRpcRequest($contentType);
        $this->assertFalse(empty($message->getCorrelationId()));
    }

    public function test_emptyRpcResponse_buildsProperRpcResponse()
    {
        $contentType = MessageInterface::CONTENT_TYPE_PLAIN_TEXT;
        $correlationId = 123;
        $message = MessagesBuilder::emptyRpcResponse($contentType, $correlationId);
        $this->assertTrue($message instanceof RpcResponseMessage);
        $this->assertEquals($correlationId, $message->getCorrelationId());
        $this->assertEquals($contentType, $message->getContentType());
        $this->assertFalse($message->getPayload()->hasErrors());
        $this->assertEquals('', $message->getPayload()->getResponse());
        $this->assertEquals(RpcResponsePayload::STATUS_CODE_UNDEFINED ,
                            $message->getPayload()->getStatus());
    }
}