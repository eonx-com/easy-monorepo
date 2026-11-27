<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Common\Listener;

use EonX\EasySwoole\Common\Enum\RequestAttribute;
use EonX\EasySwoole\Common\Listener\AccessLogListener;
use EonX\EasySwoole\Logging\Formatter\HttpFoundationAccessLogFormatter;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\UrlStringSanitizer;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class AccessLogListenerTest extends AbstractUnitTestCase
{
    /**
     * @see testItSucceedsWithSensitiveDataSanitizer
     */
    public static function provideSensitiveDataUris(): iterable
    {
        yield 'token in the query' => [
            'uri' => '/forgot-password-confirm?token=abc',
            'expectedUri' => '/forgot-password-confirm?token=*REDACTED*',
        ];

        yield 'nested key in the query' => [
            'uri' => '/login?credentials[token]=abc',
            'expectedUri' => '/login?credentials%5Btoken%5D=*REDACTED*',
        ];

        yield 'token as the last path segment' => [
            'uri' => '/reset/token/abc',
            'expectedUri' => '/reset/token/*REDACTED*',
        ];

        yield 'token in the middle of the path' => [
            'uri' => '/reset/token/abc/confirm',
            'expectedUri' => '/reset/token/*REDACTED*/confirm',
        ];
    }

    #[DataProvider('provideSensitiveDataUris')]
    public function testItSucceedsWithSensitiveDataSanitizer(string $uri, string $expectedUri): void
    {
        $handler = new TestHandler();
        $listener = new AccessLogListener(
            new Logger('test', [$handler]),
            new HttpFoundationAccessLogFormatter(logQueryString: true),
            [],
            new SensitiveDataSanitizer(['token'], '*REDACTED*', [], [new UrlStringSanitizer()])
        );

        $listener($this->createTerminateEvent($uri));

        $records = $handler->getRecords();
        self::assertSame(\sprintf('127.0.0.1 - "GET %s HTTP/1.1" 200 "Symfony"', $expectedUri), $records[0]->message);
    }

    public function testItSucceedsWithoutSensitiveDataSanitizer(): void
    {
        $handler = new TestHandler();
        $listener = new AccessLogListener(
            new Logger('test', [$handler]),
            new HttpFoundationAccessLogFormatter(logQueryString: true),
            []
        );

        $listener($this->createTerminateEvent('/forgot-password-confirm?token=abc'));

        $records = $handler->getRecords();
        self::assertSame(
            '127.0.0.1 - "GET /forgot-password-confirm?token=abc HTTP/1.1" 200 "Symfony"',
            $records[0]->message
        );
    }

    private function createTerminateEvent(string $uri): TerminateEvent
    {
        $request = Request::create($uri);
        $request->attributes->set(RequestAttribute::EasySwooleEnabled->value, true);

        return new TerminateEvent(self::createStub(HttpKernelInterface::class), $request, new Response());
    }
}
