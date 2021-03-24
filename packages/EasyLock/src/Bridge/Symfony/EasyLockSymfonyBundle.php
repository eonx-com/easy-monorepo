<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony;

use EonX\EasyLock\Bridge\Symfony\DependencyInjection\EasyLockExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyLockSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyLockExtension();
    }
}
