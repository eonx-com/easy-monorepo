<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Exceptions;

use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use Monolog\Logger;

final class BaseExceptionTest extends AbstractTestCase
{
    public function testGetLogLevel(): void
    {
        $logLevel = Logger::CRITICAL;
        $exception = (new BaseExceptionStub())->setLogLevel($logLevel);

        self::assertSame($logLevel, $exception->getLogLevel());
    }

    public function testGetMessageParams(): void
    {
        $messageParams = ['foo' => 'bar'];
        $exception = (new BaseExceptionStub())->setMessageParams($messageParams);

        self::assertSame($messageParams, $exception->getMessageParams());
    }

    public function testGetStatusCode(): void
    {
        $statusCode = 123;
        $exception = (new BaseExceptionStub())->setStatusCode($statusCode);

        self::assertSame($statusCode, $exception->getStatusCode());
    }

    public function testGetSubCode(): void
    {
        $subCode = 123;
        $exception = (new BaseExceptionStub())->setSubCode($subCode);

        self::assertSame($subCode, $exception->getSubCode());
    }

    public function testGetUserMessage(): void
    {
        $userMessage = 'User message';
        $exception = (new BaseExceptionStub())->setUserMessage($userMessage);

        self::assertSame($userMessage, $exception->getUserMessage());
    }

    public function testGetUserMessageParams(): void
    {
        $userMessageParams = ['foo' => 'bar'];
        $exception = (new BaseExceptionStub())->setUserMessageParams($userMessageParams);

        self::assertSame($userMessageParams, $exception->getUserMessageParams());
    }

    public function testSetLogLevel(): void
    {
        $logLevel = Logger::CRITICAL;

        $exception = (new BaseExceptionStub())->setLogLevel($logLevel);

        $property = $this->getPropertyAsPublic(BaseExceptionStub::class, 'logLevel');
        self::assertSame($logLevel, $property->getValue($exception));
    }

    public function testSetMessageParams(): void
    {
        $messageParams = ['foo' => 'bar'];

        $exception = (new BaseExceptionStub())->setMessageParams($messageParams);

        $property = $this->getPropertyAsPublic(BaseExceptionStub::class, 'messageParams');
        self::assertSame($messageParams, $property->getValue($exception));
    }

    public function testSetStatusCode(): void
    {
        $statusCode = 123;

        $exception = (new BaseExceptionStub())->setStatusCode($statusCode);

        $property = $this->getPropertyAsPublic(BaseExceptionStub::class, 'statusCode');
        self::assertSame($statusCode, $property->getValue($exception));
    }

    public function testSetSubCode(): void
    {
        $subCode = 123;

        $exception = (new BaseExceptionStub())->setSubCode($subCode);

        $property = $this->getPropertyAsPublic(BaseExceptionStub::class, 'subCode');
        self::assertSame($subCode, $property->getValue($exception));
    }

    public function testSetUserMessage(): void
    {
        $userMessage = 'User message';

        $exception = (new BaseExceptionStub())->setUserMessage($userMessage);

        $property = $this->getPropertyAsPublic(BaseExceptionStub::class, 'userMessage');
        self::assertSame($userMessage, $property->getValue($exception));
    }

    public function testSetUserMessageParams(): void
    {
        $userMessageParams = ['foo' => 'bar'];

        $exception = (new BaseExceptionStub())->setUserMessageParams($userMessageParams);

        $property = $this->getPropertyAsPublic(BaseExceptionStub::class, 'userMessageParams');
        self::assertSame($userMessageParams, $property->getValue($exception));
    }
}
