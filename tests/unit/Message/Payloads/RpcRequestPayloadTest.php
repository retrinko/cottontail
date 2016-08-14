<?php

namespace Retrinko\CottonTail\Tests\Unit\Message\Payloads;

use Retrinko\CottonTail\Message\Payloads\RpcRequestPayload;

class RpcRequestPayloadTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Retrinko\CottonTail\Exceptions\PayloadException
     */
    public function test_create_withEmptyProcedure_throwsPayloadException()
    {
        RpcRequestPayload::create('', []);
    }


    public function test_create_withProperParams_returnsProperRpcRequestPayload()
    {
        $procedure = 'testProcedure';
        $params = [];
        $payload = RpcRequestPayload::create($procedure, $params);
        $this->assertTrue($payload instanceof RpcRequestPayload);
        $this->assertEquals($procedure, $payload->getProcedure());
        $this->assertEquals($params, $payload->getParams());

        $params = ['foo' => 'bar'];
        $payload = RpcRequestPayload::create($procedure, $params);
        $this->assertTrue($payload instanceof RpcRequestPayload);
        $this->assertEquals($procedure, $payload->getProcedure());
        $this->assertEquals($params, $payload->getParams());
    }

}