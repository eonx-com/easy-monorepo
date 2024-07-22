<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit;

use EonX\EasyAsync\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractUnitTestCase extends KernelTestCase
{
    use ContainerServiceTrait;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $varDir = __DIR__ . '/../Fixture/app/var';

        if ($filesystem->exists($varDir)) {
            $filesystem->remove($varDir);
        }
    }

    protected static function getKernelClass(): string
    {
        return ApplicationKernel::class;
    }
}
