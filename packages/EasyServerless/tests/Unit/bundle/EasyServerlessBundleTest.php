<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle;

use Bref\Symfony\Messenger\BrefMessengerBundle;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Messenger\Listener\ResetServicesListener;
use EonX\EasyServerless\Monolog\Processor\PhpSourceProcessor;
use EonX\EasyServerless\Tests\Unit\AbstractSymfonyTestCase;

final class EasyServerlessBundleTest extends AbstractSymfonyTestCase
{
    public function testDisableMonologSucceeds(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/monolog_disabled.php']
        )->getContainer();

        self::assertFalse($container->has(PhpSourceProcessor::class));
    }

    public function testDisableResetServicesSucceeds(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/sqs_reset_services_disabled.php'],
            bundles: [new BrefMessengerBundle()]
        )->getContainer();

        self::assertFalse($container->has(ResetServicesListener::class));
    }

    public function testResetServicesEnabledByDefault(): void
    {
        $container = $this->getKernel(
            bundles: [new BrefMessengerBundle()]
        )->getContainer();

        self::assertTrue($container->has(ResetServicesListener::class));
    }

    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertTrue($container->has(PhpSourceProcessor::class));
        self::assertTrue($container->hasParameter(ConfigParam::AssetsSeparateDomainEnabled->value));
    }
}
