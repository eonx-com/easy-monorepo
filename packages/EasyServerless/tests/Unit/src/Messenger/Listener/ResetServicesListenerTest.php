<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Messenger\Listener;

use Bref\Symfony\Messenger\BrefMessengerBundle;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use EonX\EasyServerless\Messenger\Listener\ResetServicesListener;
use EonX\EasyServerless\Tests\Stub\Kernel\FrameworkKernelStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;

final class ResetServicesListenerTest extends AbstractUnitTestCase
{
    public function testItResetsServicesWithFrameworkServicesResetter(): void
    {
        $kernel = new FrameworkKernelStub(
            'test',
            true,
            [__DIR__ . '/../../../../Fixture/config/framework_test.php'],
            [new BrefMessengerBundle()]
        );
        $kernel->boot();
        /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
        $container = $kernel->getContainer()
            ->get('test.service_container');
        /** @var \EonX\EasyServerless\Messenger\Listener\ResetServicesListener $sut */
        $sut = $container->get(ResetServicesListener::class);

        $sut(new EnvelopeDispatchedEvent());

        self::assertInstanceOf(ResetServicesListener::class, $sut);
    }
}
