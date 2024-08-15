<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit;

use EonX\EasyErrorHandler\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractUnitTestCase extends KernelTestCase
{
    use ContainerServiceTrait;
    use PrivatePropertyAccessTrait;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $var = __DIR__ . '/../Fixture/app/var';

        if ($filesystem->exists($var)) {
            $filesystem->remove($var);
        }
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected static function getKernelClass(): string
    {
        return ApplicationKernel::class;
    }
}
