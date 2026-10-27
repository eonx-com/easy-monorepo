<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\Middleware;

use EonX\EasyServerless\Laravel\EasyServerlessServiceProvider;
use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLumenTestCase;
use Illuminate\Http\Request;
use ReflectionProperty;

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

    public function testMiddlewareIsRegisteredGloballyAndRunsOnUnmatchedRoutes(): void
    {
        // Health routes rely on the Route facade, which is not available in a bare Lumen application
        $app = $this->getApplication([EasyServerlessServiceProvider::class], [
            'easy-serverless.health.enabled' => false,
        ]);
        // Lumen logs the 404 under its storage path, keep it in the gitignored var dir cleaned up after the class
        $app->useStoragePath(__DIR__ . '/../../../../var/storage');

        $response = $app->handle(Request::create('/does-not-exist'));

        self::assertSame(404, $response->getStatusCode());
        // Lumen keeps its global middleware in a protected property
        $globalMiddleware = (array)(new ReflectionProperty($app, 'middleware'))->getValue($app);
        self::assertContains(PhpRuntimeInfoMiddleware::class, $globalMiddleware);
    }
}
