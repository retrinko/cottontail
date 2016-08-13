<?php


namespace Retrinko\CottonTail\Tests\Unit\Message\Messages;

use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\PayloadInterface;

class RpcRequestMessageTest extends \PHPUnit_Framework_TestCase
{
    public function test_constructor_withProperParams_populateBodyAndPropertiesProperly()
    {
        $contentType = MessageInterface::CONTENT_TYPE_PLAIN_TEXT;
        $properties = [MessageInterface::PROPERTY_CONTENT_TYPE => $contentType];
        $message = new RpcRequestMessage('', $properties);
        // timestamp
        $this->assertFalse(empty($message->getTimestamp()), 'Empty timestamp!');
        $this->assertTrue(is_int($message->getTimestamp()), 'Timestamp is not int!');
        // type
        $this->assertFalse(empty($message->getType()), 'Empty type!');
        $this->assertEquals(MessageInterface::TYPE_RPC_REQUEST, $message->getType(),
                            sprintf('Type is not "%s"!', MessageInterface::TYPE_RPC_REQUEST));
        // correlation id
        $this->assertFalse(empty($message->getCorrelationId()), 'Empty correlation id!');
        // content type
        $this->assertFalse(empty($message->getContentType()), 'Empty content type!');
        $this->assertEquals(MessageInterface::CONTENT_TYPE_PLAIN_TEXT, $message->getContentType(),
                            sprintf('Content type is not "%s"', $contentType));
        // body
        $this->assertTrue(empty($message->getBody()), 'Body is not empty!');
    }

    /**
     * @expectedException \Retrinko\CottonTail\Exceptions\MessageException
     */
    public function test_constructor_withMissingProperties_throwsMessageException()
    {
        new RpcRequestMessage('', []);
    }

    public function test_getPayload_returnsPayloadInterface()
    {
        $message = new RpcRequestMessage('',
                                         [MessageInterface::PROPERTY_CONTENT_TYPE => MessageInterface::CONTENT_TYPE_PLAIN_TEXT]);
        $payload = $message->getPayload();
        $this->assertTrue($payload instanceof PayloadInterface);
    }
}