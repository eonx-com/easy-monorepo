<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit;

use EonX\EasyRandom\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends KernelTestCase
{
    use ContainerServiceTrait;
    use PrivatePropertyAccessTrait;

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
