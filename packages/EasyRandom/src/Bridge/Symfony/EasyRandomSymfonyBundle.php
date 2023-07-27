<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony;

use EonX\EasyRandom\Bridge\Symfony\DependencyInjection\EasyRandomExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyRandomSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyRandomExtension();
    }
}
