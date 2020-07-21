<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony;

use EonX\EasyWebhook\Bridge\Symfony\DependencyInjection\Compiler\AddMessengerMiddlewarePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyWebhooksBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddMessengerMiddlewarePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
    }
}
