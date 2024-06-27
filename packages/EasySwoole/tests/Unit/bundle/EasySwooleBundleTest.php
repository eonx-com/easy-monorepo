<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Bundle;

use EonX\EasySwoole\Common\Listener\ApplicationStateCheckListener;
use EonX\EasySwoole\Common\Listener\ApplicationStateResetListener;

final class EasySwooleBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/default.php'])
            ->getContainer();

        self::assertInstanceOf(
            ApplicationStateCheckListener::class,
            $container->get(ApplicationStateCheckListener::class)
        );
        self::assertInstanceOf(
            ApplicationStateResetListener::class,
            $container->get(ApplicationStateResetListener::class)
        );
    }
}
