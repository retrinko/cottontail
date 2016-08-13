<?php

namespace Retrinko\CottonTail\Tests\Unit\Message\Messages;


use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Message\PayloadInterface;

class BasicMessageTest extends \PHPUnit_Framework_TestCase
{
    public function test_constructor_withEmptyParams_populatesDefaultMessageProperiesProperly()
    {
        $message = new BasicMessage();
        // timestamp
        $this->assertFalse(empty($message->getTimestamp()), 'Empty timestamp!');
        $this->assertTrue(is_int($message->getTimestamp()), 'Timestamp is not int!');
        // type
        $this->assertFalse(empty($message->getType()), 'Empty type!');
        $this->assertEquals(MessageInterface::TYPE_BASIC, $message->getType(),
                            sprintf('Type is not "%s"!', MessageInterface::TYPE_BASIC));
        // content type
        $this->assertFalse(empty($message->getContentType()), 'Empty content type!');
        $this->assertEquals(MessageInterface::CONTENT_TYPE_PLAIN_TEXT, $message->getContentType(),
                            sprintf('Content type is not "%s"', MessageInterface::CONTENT_TYPE_PLAIN_TEXT));
        // body
        $this->assertTrue(empty($message->getBody()), 'Body is not empty!');
    }

    public function test_constructor_withBodyParam_populatesMessageBodyProperly()
    {
        $body = 'Test message';
        $message = new BasicMessage($body);
        $this->assertEquals($body, $message->getBody());
    }

    public function test_constructor_withBodyAndPropertiesParams_populatesMessageProperly()
    {
        $body = 'Test message';
        $timestamp = 123;
        $type = 'foo';
        $foo = 'bar';
        $properties = [MessageInterface::PROPERTY_TYPE => $type,
                       MessageInterface::PROPERTY_TIMESTAMP => $timestamp,
                       'foo' => $foo];
        $message = new BasicMessage($body, $properties);

        $this->assertEquals($body, $message->getBody());
        $this->assertEquals($timestamp, $message->getTimestamp());
        $this->assertEquals($type, $message->getType());
        $this->assertEquals($foo, $message->getProperty('foo'));
        $this->assertArraySubset($properties, $message->getProperties());

    }

    public function test_getPayload_returnsPayloadInterface()
    {
        $message = new BasicMessage();
        $payload = $message->getPayload();

        $this->assertTrue($payload instanceof PayloadInterface);
    }

}