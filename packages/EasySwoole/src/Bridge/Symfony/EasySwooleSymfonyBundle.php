<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony;

use EonX\EasySwoole\Bridge\Symfony\DependencyInjection\EasySwooleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasySwooleSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasySwooleExtension();
    }
}
