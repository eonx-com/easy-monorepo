<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony;

use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler\DecorateDefaultClientPass;
use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler\DecorateEasyWebhookClientPass;
use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler\DecorateMessengerSqsClientPass;
use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\EasyHttpClientExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyHttpClientSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DecorateDefaultClientPass())
            ->addCompilerPass(new DecorateEasyWebhookClientPass())
            ->addCompilerPass(new DecorateMessengerSqsClientPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyHttpClientExtension();
    }
}
