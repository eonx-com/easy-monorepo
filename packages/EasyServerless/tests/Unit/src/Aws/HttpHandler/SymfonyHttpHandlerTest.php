<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Aws\HttpHandler;

use Bref\Context\Context;
use Bref\Event\Http\HttpRequestEvent;
use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class SymfonyHttpHandlerTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        \putenv('APP_RUNTIME_MODE');
        \putenv('LAMBDA_REQUEST_CONTEXT');
        unset(
            $_ENV['APP_RUNTIME_MODE'],
            $_SERVER['APP_RUNTIME_MODE'],
            $_ENV['LAMBDA_REQUEST_CONTEXT'],
            $_SERVER['LAMBDA_REQUEST_CONTEXT'],
        );

        parent::tearDown();
    }

    public function testHandleRequestDoesNotOverrideExplicitRuntimeMode(): void
    {
        \putenv('APP_RUNTIME_MODE=custom=1');
        $_ENV['APP_RUNTIME_MODE'] = $_SERVER['APP_RUNTIME_MODE'] = 'custom=1';

        $sut = $this->createSymfonyHttpHandler();

        $sut->handleRequest($this->createHttpRequestEvent(), Context::fake());

        self::assertSame('custom=1', \getenv('APP_RUNTIME_MODE'));
        self::assertSame('custom=1', $_ENV['APP_RUNTIME_MODE']);
        self::assertSame('custom=1', $_SERVER['APP_RUNTIME_MODE']);
    }

    public function testHandleRequestDoesNotOverrideExplicitSuperglobalRuntimeMode(): void
    {
        $_ENV['APP_RUNTIME_MODE'] = $_SERVER['APP_RUNTIME_MODE'] = 'custom=1';

        $sut = $this->createSymfonyHttpHandler();

        $sut->handleRequest($this->createHttpRequestEvent(), Context::fake());

        self::assertFalse(\getenv('APP_RUNTIME_MODE'));
        self::assertSame('custom=1', $_ENV['APP_RUNTIME_MODE']);
        self::assertSame('custom=1', $_SERVER['APP_RUNTIME_MODE']);
    }

    public function testHandleRequestSetsHttpRuntimeModeWhenMissing(): void
    {
        $sut = $this->createSymfonyHttpHandler();

        $sut->handleRequest($this->createHttpRequestEvent(), Context::fake());

        self::assertSame('web=1&worker=1', \getenv('APP_RUNTIME_MODE'));
        self::assertSame('web=1&worker=1', $_ENV['APP_RUNTIME_MODE']);
        self::assertSame('web=1&worker=1', $_SERVER['APP_RUNTIME_MODE']);
    }

    private function createHttpRequestEvent(): HttpRequestEvent
    {
        return new HttpRequestEvent([
            'headers' => [],
            'httpMethod' => 'GET',
            'path' => '/some-path',
            'requestContext' => [],
        ]);
    }

    private function createSymfonyHttpHandler(): SymfonyHttpHandler
    {
        $request = new Request();
        $kernel = $this->createMock(KernelInterface::class);
        $kernel
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn(new Response());

        $httpFoundationFactory = $this->createMock(HttpFoundationFactoryInterface::class);
        $httpFoundationFactory
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        return new SymfonyHttpHandler($kernel, $httpFoundationFactory);
    }
}
