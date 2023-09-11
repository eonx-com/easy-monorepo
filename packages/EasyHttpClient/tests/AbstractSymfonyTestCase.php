<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests;

use EonX\EasyHttpClient\Tests\Bridge\Symfony\Fixtures\App\Kernel\ApplicationKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel('test', false);
    }
}
