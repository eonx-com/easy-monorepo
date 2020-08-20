<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Laravel;

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

final class EasyPsr7FactoryServiceProviderTest extends AbstractLumenTestCase
{
    public function testRegisterExpectedServices(): void
    {
        $app = $this->getApplication(new Request($query ?? [], [], [], [], [], ['HTTP_HOST' => 'eonx.com']));

        self::assertInstanceOf(EasyPsr7Factory::class, $app->get(EasyPsr7FactoryInterface::class));
        self::assertInstanceOf(ServerRequestInterface::class, $app->get(ServerRequestInterface::class));
    }
}
