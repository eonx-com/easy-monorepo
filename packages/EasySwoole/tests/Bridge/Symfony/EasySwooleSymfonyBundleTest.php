<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Bridge\Symfony;

use EonX\EasySwoole\Bridge\Symfony\Listeners\ApplicationStateCheckListener;
use EonX\EasySwoole\Bridge\Symfony\Listeners\ApplicationStateResetListener;

final class EasySwooleSymfonyBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/config/default.yaml'])
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
