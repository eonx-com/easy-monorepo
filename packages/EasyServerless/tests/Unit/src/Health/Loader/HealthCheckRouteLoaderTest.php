<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Health\Loader;

use EonX\EasyServerless\Health\Controller\HealthCheckController;
use EonX\EasyServerless\Health\Loader\HealthCheckRouteLoader;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

final class HealthCheckRouteLoaderTest extends AbstractUnitTestCase
{
    private const string ROUTE_TYPE = 'route_type';

    /**
     * @see testSupportsSucceeds
     */
    public static function provideTypes(): iterable
    {
        yield 'Configured route type' => [
            'type' => self::ROUTE_TYPE,
            'expectedResult' => true,
        ];

        yield 'Another route type' => [
            'type' => 'another_route_type',
            'expectedResult' => false,
        ];

        yield 'No route type' => [
            'type' => null,
            'expectedResult' => false,
        ];
    }

    public function testLoadSucceeds(): void
    {
        $loader = new HealthCheckRouteLoader(self::ROUTE_TYPE);

        $routes = $loader->load('.', self::ROUTE_TYPE);

        $route = $routes->get('easy_serverless.health_check');
        self::assertCount(1, $routes);
        self::assertNotNull($route);
        self::assertSame('/easy-serverless/health-check', $route->getPath());
        self::assertSame(HealthCheckController::class, $route->getDefault('_controller'));
        self::assertSame([Request::METHOD_GET], $route->getMethods());
    }

    public function testLoadThrowsRuntimeExceptionWhenLoadedTwice(): void
    {
        $loader = new HealthCheckRouteLoader(self::ROUTE_TYPE);
        $loader->load('.', self::ROUTE_TYPE);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Do not add the "route_type" loader twice');

        $loader->load('.', self::ROUTE_TYPE);
    }

    #[DataProvider('provideTypes')]
    public function testSupportsSucceeds(?string $type, bool $expectedResult): void
    {
        $loader = new HealthCheckRouteLoader(self::ROUTE_TYPE);

        $result = $loader->supports('.', $type);

        self::assertSame($expectedResult, $result);
    }
}
