<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Laravel\Lumen\Application;
use EonX\EasyPagination\Resolvers\Config\StartSizeConfig;
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

    /**
     * Assert given abstract is an instance of concrete in the application container.
     *
     * @param string $concrete
     * @param string $abstract
     *
     * @return void
     */
    protected function assertInstanceInApp(string $concrete, string $abstract): void
    {
        self::assertInstanceOf($concrete, $this->getApplication()->get($abstract));
    }

    /**
     * Create StartSizeConfig.
     *
     * @param null|string $numberAttr
     * @param null|int $numberDefault
     * @param null|string $sizeAttr
     * @param null|int $sizeDefault
     *
     * @return \EonX\EasyPagination\Resolvers\Config\StartSizeConfig
     */
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
     * Create server request for given query.
     *
     * @param null|mixed[] $query
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function createServerRequest(?array $query = null): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals(null, $query);
    }

    /**
     * Get lumen application.
     *
     * @return \Laravel\Lumen\Application
     */
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


