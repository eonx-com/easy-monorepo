<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony;

use EonX\EasyUtils\Bridge\Symfony\DependencyInjection\EasyUtilsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyUtilsSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyUtilsExtension();
    }
}
