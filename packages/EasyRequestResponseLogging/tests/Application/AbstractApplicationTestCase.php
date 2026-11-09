<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Application;

use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Kernel\ApplicationKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractApplicationTestCase extends KernelTestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $var = __DIR__ . '/../Fixture/app/var';

        if ($filesystem->exists($var)) {
            $filesystem->remove($var);
        }
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel($options['environment'] ?? 'test', false);
    }
}
