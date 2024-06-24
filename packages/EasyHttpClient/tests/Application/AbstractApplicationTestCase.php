<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Application;

use EonX\EasyHttpClient\Tests\Fixture\App\Kernel\ApplicationKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractApplicationTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $fs = new Filesystem();
        $var = __DIR__ . '/../Fixture/app/var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel('test', false);
    }
}
