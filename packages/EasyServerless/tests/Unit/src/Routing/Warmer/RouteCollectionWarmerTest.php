<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Routing\Warmer;

use EonX\EasyServerless\Routing\Warmer\RouteCollectionWarmer;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class RouteCollectionWarmerTest extends AbstractUnitTestCase
{
    public function testWarmDoesNothingWithoutRouter(): void
    {
        $sut = new RouteCollectionWarmer();

        $sut->warm();

        $this->addToAssertionCount(1);
    }

    public function testWarmLoadsRouteCollection(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects(self::once())
            ->method('getRouteCollection')
            ->willReturn(new RouteCollection());
        $sut = new RouteCollectionWarmer($router);

        $sut->warm();
    }
}
