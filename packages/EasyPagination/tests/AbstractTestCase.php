<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use EonX\EasyPagination\Resolvers\Config\StartSizeConfig;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    protected function assertInstanceInApp(string $concrete, string $abstract): void
    {
        self::assertInstanceOf($concrete, $this->getApplication()->get($abstract));
    }

    protected function createConfig(
        ?string $numberAttr = null,
        ?int $numberDefault = null,
        ?string $sizeAttr = null,
        ?int $sizeDefault = null
    ): StartSizeConfig {
        return new StartSizeConfig(
            $numberAttr ?? 'number',
            $numberDefault ?? 1,
            $sizeAttr ?? 'size',
            $sizeDefault ?? 15
        );
    }

    /**
     * @param null|mixed[] $query
     */
    protected function createServerRequest(?array $query = null): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals(null, $query);
    }

    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = $this->app = new Application(__DIR__);

        $app->instance(ServerRequestInterface::class, $this->createServerRequest());

        return $app;
    }
}
