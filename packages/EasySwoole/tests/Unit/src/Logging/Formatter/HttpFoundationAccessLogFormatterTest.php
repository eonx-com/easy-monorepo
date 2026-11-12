<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Logging\Formatter;

use EonX\EasySwoole\Logging\Formatter\HttpFoundationAccessLogFormatter;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpFoundationAccessLogFormatterTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $formatter = new HttpFoundationAccessLogFormatter();
        $request = Request::create('/forgot-password-confirm?email=jane%40example.com&token=abc');

        $result = $formatter->formatAccessLog($request, new Response());

        self::assertSame('127.0.0.1 - "GET /forgot-password-confirm HTTP/1.1" 200 "Symfony"', $result);
    }

    public function testItSucceedsWithQueryString(): void
    {
        $formatter = new HttpFoundationAccessLogFormatter(logQueryString: true);
        $request = Request::create('/forgot-password-confirm?email=jane%40example.com&token=abc');

        $result = $formatter->formatAccessLog($request, new Response());

        self::assertSame(
            '127.0.0.1 - "GET /forgot-password-confirm?email=jane%40example.com&token=abc HTTP/1.1" 200 "Symfony"',
            $result
        );
    }
}
