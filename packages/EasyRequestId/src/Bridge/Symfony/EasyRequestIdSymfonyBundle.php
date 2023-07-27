<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony;

use EonX\EasyRequestId\Bridge\Symfony\DependencyInjection\EasyRequestIdExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyRequestIdSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyRequestIdExtension();
    }
}
