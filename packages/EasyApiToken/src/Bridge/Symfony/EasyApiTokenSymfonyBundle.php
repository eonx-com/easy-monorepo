<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge\Symfony;

use EonX\EasyApiToken\Bridge\Symfony\DependencyInjection\EasyApiTokenExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyApiTokenSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyApiTokenExtension();
    }
}
