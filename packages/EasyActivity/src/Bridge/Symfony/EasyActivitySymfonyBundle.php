<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony;

use EonX\EasyActivity\Bridge\Symfony\DependencyInjection\EasyActivityExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyActivitySymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyActivityExtension();
    }
}
