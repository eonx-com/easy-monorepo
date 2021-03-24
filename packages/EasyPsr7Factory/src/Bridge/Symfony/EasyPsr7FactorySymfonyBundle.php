<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Bridge\Symfony;

use EonX\EasyPsr7Factory\Bridge\Symfony\DependencyInjection\EasyPsr7FactoryExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyPsr7FactorySymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyPsr7FactoryExtension();
    }
}
