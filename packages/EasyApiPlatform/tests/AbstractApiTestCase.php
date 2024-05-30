<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Traits\ArrayAssertionTrait;
use EonX\EasyTest\Traits\ContainerServiceTrait;
use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractApiTestCase extends ApiTestCase
{
    use ArrayAssertionTrait;
    use ContainerServiceTrait;
    use PrivatePropertyAccessTrait;

    protected static Client $client;

    protected function setUp(): void
    {
        static::$client = static::createClient(
            [],
            [
                'headers' => ['accept' => ['application/json']],
            ]
        );
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel('test', false);
    }
}
