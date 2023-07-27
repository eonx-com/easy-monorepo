<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bridge\Symfony;

use EonX\EasyEncryption\Bridge\Symfony\DependencyInjection\EasyEncryptionExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyEncryptionSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyEncryptionExtension();
    }
}
