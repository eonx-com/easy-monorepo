<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle;

use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use EonX\EasyServerless\Messenger\Listener\ResetServicesListener;
use EonX\EasyServerless\Tests\Stub\Bundle\BrefMessengerBundle;
use EonX\EasyServerless\Monolog\Processor\PhpSourceProcessor;
use EonX\EasyServerless\Tests\Stub\Resetter\ServicesResetterStub;
use EonX\EasyServerless\Tests\Unit\AbstractSymfonyTestCase;

final class EasyServerlessBundleTest extends AbstractSymfonyTestCase
{
    public function testMessengerResetServicesListenerSucceeds(): void
    {
        $container = $this->getKernel(extraBundles: [BrefMessengerBundle::class])
            ->getContainer();

        self::assertTrue($container->has(ResetServicesListener::class));

        $listener = $container->get(ResetServicesListener::class);
        $servicesResetter = $container->get('services_resetter');

        self::assertInstanceOf(ResetServicesListener::class, $listener);
        self::assertInstanceOf(ServicesResetterStub::class, $servicesResetter);

        $listener->resetServices(new EnvelopeDispatchedEvent());

        self::assertSame(1, $servicesResetter->getResetCalls());
    }

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
