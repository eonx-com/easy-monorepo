<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\Middleware;

use EonX\EasyServerless\Laravel\EasyServerlessServiceProvider;
use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Tests\Stub\Middleware\ShortCircuitMiddlewareStub;
use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLumenTestCase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use ReflectionProperty;

use function DDTrace\root_span;

final class PhpRuntimeInfoMiddlewareTest extends AbstractLumenTestCase
{
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

    #[RunInSeparateProcess]
    public function testMiddlewareRunsBeforeGlobalMiddlewareReturningEarly(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../Fixture/ddtrace/ddtrace.php';
        // Health routes rely on the Route facade, which is not available in a bare Lumen application
        $app = $this->getApplication([], [
            'easy-serverless.health.enabled' => false,
        ]);
        // Application middleware is usually registered before the providers in bootstrap/app.php
        $app->middleware([ShortCircuitMiddlewareStub::class]);
        $app->register(EasyServerlessServiceProvider::class);

        $response = $app->handle(Request::create('/does-not-exist'));

        self::assertSame(ShortCircuitMiddlewareStub::RESPONSE_CONTENT, $response->getContent());
        self::assertSame(\PHP_VERSION, root_span()?->meta['php.version'] ?? null);
        // Lumen keeps its global middleware in a protected property
        self::assertSame(
            [PhpRuntimeInfoMiddleware::class, ShortCircuitMiddlewareStub::class],
            (new ReflectionProperty($app, 'middleware'))->getValue($app)
        );
    }
}
