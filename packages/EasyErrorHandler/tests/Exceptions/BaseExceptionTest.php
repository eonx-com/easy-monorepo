<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Exceptions;

use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use Monolog\Logger;

final class BaseExceptionTest extends AbstractTestCase
{
    /**
     * @see testLogLevelConvenientMethods
     */
    public static function providerTestLogLevelConvenientMethods(): iterable
    {
        yield 'critical' => [
            'method' => 'setCriticalLogLevel',
            'expectedLogLevel' => Logger::CRITICAL,
        ];

        yield 'debug' => [
            'method' => 'setDebugLogLevel',
            'expectedLogLevel' => Logger::DEBUG,
        ];

        yield 'error' => [
            'method' => 'setErrorLogLevel',
            'expectedLogLevel' => Logger::ERROR,
        ];

        yield 'info' => [
            'method' => 'setInfoLogLevel',
            'expectedLogLevel' => Logger::INFO,
        ];

        yield 'warning' => [
            'method' => 'setWarningLogLevel',
            'expectedLogLevel' => Logger::WARNING,
        ];
    }

    public function testGetLogLevel(): void
    {
        $logLevel = Logger::CRITICAL;
        $exception = new BaseExceptionStub();
        $this->setPrivatePropertyValue($exception, 'logLevel', $logLevel);

        $result = $exception->getLogLevel();

        self::assertSame($logLevel, $result);
    }

    public function testGetMessageParams(): void
    {
        $messageParams = [
            'foo' => 'bar',
        ];
        $exception = new BaseExceptionStub();
        $this->setPrivatePropertyValue($exception, 'messageParams', $messageParams);

        $result = $exception->getMessageParams();

        self::assertSame($messageParams, $result);
    }

    public function testGetStatusCode(): void
    {
        $statusCode = 123;
        $exception = new BaseExceptionStub();
        $this->setPrivatePropertyValue($exception, 'statusCode', $statusCode);

        $result = $exception->getStatusCode();

        self::assertSame($statusCode, $result);
    }

    public function testGetSubCode(): void
    {
        $subCode = 123;
        $exception = new BaseExceptionStub();
        $this->setPrivatePropertyValue($exception, 'subCode', $subCode);

        $result = $exception->getSubCode();

        self::assertSame($subCode, $result);
    }

    public function testGetUserMessage(): void
    {
        $userMessage = 'User message';
        $exception = new BaseExceptionStub();
        $this->setPrivatePropertyValue($exception, 'userMessage', $userMessage);

        $result = $exception->getUserMessage();

        self::assertSame($userMessage, $result);
    }

    public function testGetUserMessageParams(): void
    {
        $userMessageParams = [
            'foo' => 'bar',
        ];
        $exception = new BaseExceptionStub();
        $this->setPrivatePropertyValue($exception, 'userMessageParams', $userMessageParams);

        $result = $exception->getUserMessageParams();

        self::assertSame($userMessageParams, $result);
    }

    /**
     * @dataProvider providerTestLogLevelConvenientMethods
     */
    public function testLogLevelConvenientMethods(string $method, int $expectedLogLevel): void
    {
        $exception = new BaseExceptionStub();

        $result = $exception->{$method}();

        self::assertSame($exception, $result);
        self::assertSame($expectedLogLevel, $this->getPrivatePropertyValue($result, 'logLevel'));
    }

    public function testSetLogLevel(): void
    {
        $logLevel = Logger::CRITICAL;
        $exception = new BaseExceptionStub();

        $result = $exception->setLogLevel($logLevel);

        self::assertSame($exception, $result);
        self::assertSame($logLevel, $this->getPrivatePropertyValue($result, 'logLevel'));
    }

    public function testSetMessageParams(): void
    {
        $messageParams = [
            'foo' => 'bar',
        ];
        $exception = new BaseExceptionStub();

        $result = $exception->setMessageParams($messageParams);

        self::assertSame($exception, $result);
        self::assertSame($messageParams, $this->getPrivatePropertyValue($result, 'messageParams'));
    }

    public function testSetStatusCode(): void
    {
        $statusCode = 123;
        $exception = new BaseExceptionStub();

        $result = $exception->setStatusCode($statusCode);

        self::assertSame($exception, $result);
        self::assertSame($statusCode, $this->getPrivatePropertyValue($result, 'statusCode'));
    }

    public function testSetSubCode(): void
    {
        $subCode = 123;
        $exception = new BaseExceptionStub();

        $result = $exception->setSubCode($subCode);

        self::assertSame($exception, $result);
        self::assertSame($subCode, $this->getPrivatePropertyValue($result, 'subCode'));
    }

    public function testSetUserMessage(): void
    {
        $userMessage = 'User message';
        $exception = new BaseExceptionStub();

        $result = $exception->setUserMessage($userMessage);

        self::assertSame($exception, $result);
        self::assertSame($userMessage, $this->getPrivatePropertyValue($result, 'userMessage'));
    }

    public function testSetUserMessageParams(): void
    {
        $userMessageParams = [
            'foo' => 'bar',
        ];
        $exception = new BaseExceptionStub();

        $result = $exception->setUserMessageParams($userMessageParams);

        self::assertSame($exception, $result);
        self::assertSame($userMessageParams, $this->getPrivatePropertyValue($result, 'userMessageParams'));
    }
}
