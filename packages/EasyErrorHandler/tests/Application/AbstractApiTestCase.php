<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Application;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use EonX\EasyErrorHandler\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Traits\ArrayAssertionTrait;
use EonX\EasyTest\Traits\ContainerServiceTrait;
use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use Symfony\Component\Filesystem\Filesystem;
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $fs = new Filesystem();
        $files = [__DIR__ . '/../Fixture/app/var'];

        foreach ($files as $file) {
            if ($fs->exists($file)) {
                $fs->remove($file);
            }
        }
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel('test', false);
    }
}
