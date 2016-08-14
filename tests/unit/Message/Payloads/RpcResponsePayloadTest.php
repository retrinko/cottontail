<?php

namespace Retrinko\CottonTail\Tests\Unit\Message\Payloads;

use Retrinko\CottonTail\Message\Payloads\RpcResponsePayload;

class RpcResponsePayloadTest extends \PHPUnit_Framework_TestCase
{

    public function test_create_withDefaultParams_returnsProperRpcResponsePayload()
    {
        $payload = RpcResponsePayload::create();

        $this->assertTrue($payload instanceof RpcResponsePayload);
        $this->assertEquals('', $payload->getResponse());
        $this->assertEquals([], $payload->getErrors());
        $this->assertEquals(RpcResponsePayload::STATUS_CODE_SUCCESS, $payload->getStatus());
        $this->assertTrue($payload->isOK());
        $this->assertFalse($payload->hasErrors());
    }


    public function test_create_withErrors_returnsProperRpcRequestPayload()
    {
        $response = '';
        $errors = ['error 1', 'error 2'];
        $code = 100;
        $payload = RpcResponsePayload::create($response, $code, $errors);

        $this->assertTrue($payload instanceof RpcResponsePayload);
        $this->assertEquals($response, $payload->getResponse());
        $this->assertEquals($errors, $payload->getErrors());
        $this->assertEquals(RpcResponsePayload::STATUS_CODE_ERROR, $payload->getStatus());
        $this->assertFalse($payload->isOK());
        $this->assertTrue($payload->hasErrors());

    }

    /**
     * @expectedException \Retrinko\CottonTail\Exceptions\PayloadException
     */
    public function test_setResponse_withInvalidParams_throwsPayloadException()
    {
        $payload = RpcResponsePayload::create();

        $payload->setResponse(new \ArrayObject());
    }

}