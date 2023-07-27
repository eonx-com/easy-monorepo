<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Symfony;

use EonX\EasyEventDispatcher\Bridge\Symfony\DependencyInjection\EasyEventDispatcherExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyEventDispatcherSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyEventDispatcherExtension();
    }
}
