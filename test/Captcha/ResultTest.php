<?php

namespace LaminasTest\ReCaptcha2\Captcha;

use PHPUnit\Framework\TestCase;
use ReCaptcha2\Captcha\Result;
use Laminas\Http\Response;

class ResultTest extends TestCase
{
    public function testConstructorShouldSetHttpResponse()
    {
        $json = json_encode([
            'success' => false,
            'error-codes' => ['test'],
        ]);

        $httpResponseMock = $this->createMock(Response::class, ['getBody']);
        $httpResponseMock->expects($this->exactly(1))
            ->method('getBody')
            ->will($this->returnValue($json));

        $result = new Result($httpResponseMock);
        $this->assertFalse($result->isValid());
        $this->assertFalse($result->getStatus());
        $this->assertEquals(['test'], $result->getErrorCodes());
    }

    public function testSetStatusTrue()
    {
        $result = new Result();

        $result->setStatus(true);
        $this->assertTrue($result->getStatus());
        $this->assertTrue($result->isValid());
    }

    public function testSetStatusFalse()
    {
        $result = new Result();

        $result->setStatus(false);
        $this->assertFalse($result->getStatus());
        $this->assertFalse($result->isValid());
    }

    public function testSetErrorCodes()
    {
        $result = new Result();
        $errorCodes = ['test', 'foo', 'bar'];

        $result->setErrorCodes($errorCodes);
        $this->assertEquals($errorCodes, $result->getErrorCodes());
    }

    public function testSetErrorCodesStringGetterReturnsArray()
    {
        $result = new Result();

        $result->setErrorCodes('this is a string');
        $this->assertIsArray($result->getErrorCodes());
        $this->assertEquals(['this is a string'], $result->getErrorCodes());
    }

    public function httpResponseDataProvider()
    {
        return [
            [true, ['test'],            true, ['test']],
            [false, ['test'],           false, ['test']],
            ['test', ['a', 'b', 'c'],   true, ['a', 'b', 'c']],
            [null, 'hello world',       false, ['hello world']],
        ];
    }

    /**
     * @dataProvider httpResponseDataProvider
     */
    public function testSetFromHttpResponse($status, $errorCodes, $expectedStatus, $expectedErrorCodes)
    {
        $json = json_encode([
            'success' => $status,
            'error-codes' => $errorCodes,
        ]);

        $httpResponseMock = $this->createMock(Response::class, ['getBody']);
        $httpResponseMock->expects($this->exactly(1))
            ->method('getBody')
            ->will($this->returnValue($json));

        $result = new Result($httpResponseMock);
        $this->assertEquals($expectedStatus, $result->isValid());
        $this->assertEquals($expectedStatus, $result->getStatus());
        $this->assertEquals($expectedErrorCodes, $result->getErrorCodes());
    }
}
