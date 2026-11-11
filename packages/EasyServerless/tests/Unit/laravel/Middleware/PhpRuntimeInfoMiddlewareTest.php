<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\Middleware;

use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLumenTestCase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

use function DDTrace\root_span;

final class PhpRuntimeInfoMiddlewareTest extends AbstractLumenTestCase
{
    #[RunInSeparateProcess]
    public function testHandleAddsPhpRuntimeInfoToRootSpan(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../Fixture/ddtrace/ddtrace.php';

        (new PhpRuntimeInfoMiddleware())->handle(Request::create('/'), static fn (): string => 'response');

        self::assertSame(\PHP_VERSION, root_span()?->meta['php.version'] ?? null);
    }

    public function testHandleCallsNext(): void
    {
        $request = Request::create('/');
        $called = false;

        $response = (new PhpRuntimeInfoMiddleware())->handle($request, static function (Request $next) use (
            $request,
            &$called,
        ): string {
            $called = $next === $request;

            return 'response';
        });

        self::assertTrue($called);
        self::assertSame('response', $response);
    }
}
