<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Aws\Warmer;

use ArrayObject;
use Bref\Symfony\Messenger\Service\Sqs\SqsConsumer;
use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;
use EonX\EasyServerless\Aws\Warmer\SymfonyHttpHandlerWarmer;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

final class SymfonyHttpHandlerWarmerTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        \putenv('_HANDLER');
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

    /**
     * @see testWarmThrowsAndTerminatesKernelWhenResponseIsNotSuccessful
     */
    public static function provideNotSuccessfulStatusCodeData(): iterable
    {
        yield 'redirection' => [
            'statusCode' => 302,
        ];

        yield 'client error' => [
            'statusCode' => 404,
        ];

        yield 'server error' => [
            'statusCode' => 503,
        ];
    }

    /**
     * @see testWarmDoesNothingWhenHandlerIsNotSymfonyHttpHandler
     */
    public static function provideNotSymfonyHttpHandlerData(): iterable
    {
        yield 'empty handler' => [
            'handler' => '',
        ];

        yield 'php-fpm handler' => [
            'handler' => 'public/index.php',
        ];

        yield 'sqs handler' => [
            'handler' => SqsConsumer::class,
        ];

        yield 'composed handler of another class with the same name' => [
            'handler' => 'public/index.php:App\Http\SymfonyHttpHandler',
        ];
    }

    public function testWarmDoesNotSendHostHeaderWhenHostIsEmpty(): void
    {
        \putenv('_HANDLER=' . SymfonyHttpHandler::class);
        $response = new Response();
        $handledRequests = new ArrayObject();
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\HttpKernel\KernelInterface&\Symfony\Component\HttpKernel\TerminableInterface $kernel */
        $kernel = $this->createMockForIntersectionOfInterfaces([KernelInterface::class, TerminableInterface::class]);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->willReturnCallback(static function (Request $request) use ($handledRequests, $response): Response {
                $handledRequests->append($request);

                return $response;
            });
        $kernel
            ->expects(self::once())
            ->method('terminate')
            ->with(self::isInstanceOf(Request::class), $response);
        $sut = new SymfonyHttpHandlerWarmer(httpHandler: new SymfonyHttpHandler($kernel), host: '');

        $sut->warm();

        self::assertCount(1, $handledRequests);
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $handledRequests[0];
        self::assertFalse($request->headers->has('Host'));
    }

    #[DataProvider('provideNotSymfonyHttpHandlerData')]
    public function testWarmDoesNothingWhenHandlerIsNotSymfonyHttpHandler(string $handler): void
    {
        \putenv('_HANDLER=' . $handler);
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\HttpKernel\KernelInterface&\Symfony\Component\HttpKernel\TerminableInterface $kernel */
        $kernel = $this->createMockForIntersectionOfInterfaces([KernelInterface::class, TerminableInterface::class]);
        $kernel
            ->expects(self::never())
            ->method('handle');
        $kernel
            ->expects(self::never())
            ->method('terminate');
        $sut = new SymfonyHttpHandlerWarmer(new SymfonyHttpHandler($kernel));

        $sut->warm();
    }

    #[RunInSeparateProcess]
    public function testWarmHandlesHttpsRequestOfTrustedApiGatewayProxy(): void
    {
        \putenv('_HANDLER=' . SymfonyHttpHandler::class);
        Request::setTrustedProxies(
            ['127.0.0.1'],
            Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
        );
        $response = new Response();
        $handledRequests = new ArrayObject();
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\HttpKernel\KernelInterface&\Symfony\Component\HttpKernel\TerminableInterface $kernel */
        $kernel = $this->createMockForIntersectionOfInterfaces([KernelInterface::class, TerminableInterface::class]);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->willReturnCallback(static function (Request $request) use ($handledRequests, $response): Response {
                $handledRequests->append($request);

                return $response;
            });
        $kernel
            ->expects(self::once())
            ->method('terminate')
            ->with(self::isInstanceOf(Request::class), $response);
        $sut = new SymfonyHttpHandlerWarmer(new SymfonyHttpHandler($kernel));

        $sut->warm();

        self::assertCount(1, $handledRequests);
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $handledRequests[0];
        self::assertTrue($request->isSecure());
        self::assertSame('https', $request->getScheme());
        self::assertSame(443, $request->getPort());
    }

    public function testWarmHandlesRequestAndTerminatesKernel(): void
    {
        \putenv('_HANDLER=' . SymfonyHttpHandler::class);
        $response = new Response('{"status":"ok"}');
        $handledRequests = new ArrayObject();
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\HttpKernel\KernelInterface&\Symfony\Component\HttpKernel\TerminableInterface $kernel */
        $kernel = $this->createMockForIntersectionOfInterfaces([KernelInterface::class, TerminableInterface::class]);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->willReturnCallback(static function (Request $request) use ($handledRequests, $response): Response {
                $handledRequests->append($request);

                return $response;
            });
        $kernel
            ->expects(self::once())
            ->method('terminate')
            ->with(self::isInstanceOf(Request::class), $response);
        $sut = new SymfonyHttpHandlerWarmer(new SymfonyHttpHandler($kernel));

        $sut->warm();

        self::assertCount(1, $handledRequests);
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $handledRequests[0];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/easy-serverless/health-check', $request->getPathInfo());
        self::assertSame('application/json', $request->headers->get('Accept'));
        self::assertSame('443', $request->headers->get('X-Forwarded-Port'));
        self::assertSame('https', $request->headers->get('X-Forwarded-Proto'));
        self::assertTrue($request->isSecure());
        self::assertFalse($request->headers->has('Host'));
        self::assertSame('127.0.0.1', $request->getClientIp());
    }

    public function testWarmHandlesRequestWithComposedHandlerName(): void
    {
        \putenv('_HANDLER=public/index.php:' . SymfonyHttpHandler::class);
        $response = new Response();
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\HttpKernel\KernelInterface&\Symfony\Component\HttpKernel\TerminableInterface $kernel */
        $kernel = $this->createMockForIntersectionOfInterfaces([KernelInterface::class, TerminableInterface::class]);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->with(self::isInstanceOf(Request::class))
            ->willReturn($response);
        $kernel
            ->expects(self::once())
            ->method('terminate')
            ->with(self::isInstanceOf(Request::class), $response);
        $sut = new SymfonyHttpHandlerWarmer(new SymfonyHttpHandler($kernel));

        $sut->warm();
    }

    public function testWarmSendsHostHeader(): void
    {
        \putenv('_HANDLER=' . SymfonyHttpHandler::class);
        $response = new Response();
        $handledRequests = new ArrayObject();
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\HttpKernel\KernelInterface&\Symfony\Component\HttpKernel\TerminableInterface $kernel */
        $kernel = $this->createMockForIntersectionOfInterfaces([KernelInterface::class, TerminableInterface::class]);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->willReturnCallback(static function (Request $request) use ($handledRequests, $response): Response {
                $handledRequests->append($request);

                return $response;
            });
        $kernel
            ->expects(self::once())
            ->method('terminate')
            ->with(self::isInstanceOf(Request::class), $response);
        $sut = new SymfonyHttpHandlerWarmer(
            httpHandler: new SymfonyHttpHandler($kernel),
            path: '/health',
            host: 'api.example.com',
        );

        $sut->warm();

        self::assertCount(1, $handledRequests);
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $handledRequests[0];
        self::assertSame('/health', $request->getPathInfo());
        self::assertSame('api.example.com', $request->headers->get('Host'));
        self::assertSame('api.example.com', $request->getHost());
        self::assertTrue($request->isSecure());
        self::assertSame(443, $request->getPort());
        self::assertSame('https://api.example.com', $request->getSchemeAndHttpHost());
    }

    #[DataProvider('provideNotSuccessfulStatusCodeData')]
    public function testWarmThrowsAndTerminatesKernelWhenResponseIsNotSuccessful(int $statusCode): void
    {
        \putenv('_HANDLER=' . SymfonyHttpHandler::class);
        $response = new Response('', $statusCode);
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\HttpKernel\KernelInterface&\Symfony\Component\HttpKernel\TerminableInterface $kernel */
        $kernel = $this->createMockForIntersectionOfInterfaces([KernelInterface::class, TerminableInterface::class]);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->with(self::isInstanceOf(Request::class))
            ->willReturn($response);
        $kernel
            ->expects(self::once())
            ->method('terminate')
            ->with(self::isInstanceOf(Request::class), $response);
        $sut = new SymfonyHttpHandlerWarmer(new SymfonyHttpHandler($kernel));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(
            'The SnapStart warm-up request "GET /easy-serverless/health-check" returned the HTTP status code %d.',
            $statusCode
        ));

        $sut->warm();
    }
}
