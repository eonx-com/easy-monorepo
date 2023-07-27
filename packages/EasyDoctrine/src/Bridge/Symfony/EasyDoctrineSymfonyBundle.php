<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony;

use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\EasyDoctrineExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyDoctrineSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyDoctrineExtension();
    }
}
