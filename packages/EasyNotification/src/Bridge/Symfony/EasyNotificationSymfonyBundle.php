<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bridge\Symfony;

use EonX\EasyNotification\Bridge\Symfony\DependencyInjection\EasyNotificationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyNotificationSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyNotificationExtension();
    }
}
