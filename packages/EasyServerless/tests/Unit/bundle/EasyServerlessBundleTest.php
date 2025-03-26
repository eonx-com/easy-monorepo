<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle;

use EonX\EasyServerless\Tests\Unit\AbstractSymfonyTestCase;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;

final class EasyServerlessBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertTrue($container->hasParameter(ConfigParam::AssetsSeparateDomainEnabled->value));
    }
}
