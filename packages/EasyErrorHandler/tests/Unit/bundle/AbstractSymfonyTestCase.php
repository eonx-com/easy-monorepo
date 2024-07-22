<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Bundle;

use EonX\EasyErrorHandler\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends KernelTestCase
{
    use ContainerServiceTrait;
    use PrivatePropertyAccessTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $fs = new Filesystem();
        $files = [__DIR__ . '/../../Fixture/app/var'];

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
