<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ArrayAssertionTrait;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
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
