<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle;

use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Monolog\Processor\PhpSourceProcessor;
use EonX\EasyServerless\Tests\Unit\AbstractSymfonyTestCase;

final class EasyServerlessBundleTest extends AbstractSymfonyTestCase
{
    public function testDisableMonologSucceeds(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/monolog_disabled.php'])
            ->getContainer();

        self::assertFalse($container->has(PhpSourceProcessor::class));
    }

    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertTrue($container->has(PhpSourceProcessor::class));
        self::assertTrue($container->hasParameter(ConfigParam::AssetsSeparateDomainEnabled->value));
    }
}
