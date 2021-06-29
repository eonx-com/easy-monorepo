<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony;

use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\EasyHttpClientExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyHttpClientSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyHttpClientExtension();
    }
}
