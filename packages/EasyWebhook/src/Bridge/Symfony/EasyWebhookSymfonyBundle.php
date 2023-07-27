<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony;

use EonX\EasyWebhook\Bridge\Symfony\DependencyInjection\Compiler\AddMessengerMiddlewarePass;
use EonX\EasyWebhook\Bridge\Symfony\DependencyInjection\EasyWebhookExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyWebhookSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddMessengerMiddlewarePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyWebhookExtension();
    }
}
