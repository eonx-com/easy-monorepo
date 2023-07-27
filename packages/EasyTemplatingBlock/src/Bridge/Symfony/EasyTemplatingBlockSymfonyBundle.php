<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge\Symfony;

use EonX\EasyTemplatingBlock\Bridge\Symfony\DependencyInjection\EasyTemplatingBlockExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyTemplatingBlockSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyTemplatingBlockExtension();
    }
}
