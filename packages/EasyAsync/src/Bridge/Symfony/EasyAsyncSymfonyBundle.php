<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony;

use EonX\EasyAsync\Bridge\Symfony\DependencyInjection\EasyAsyncExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyAsyncSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        // The body is not required
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyAsyncExtension();
    }
}
