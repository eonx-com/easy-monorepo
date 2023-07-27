<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Bridge\Symfony\DependencyInjection;

use EonX\EasyRepository\Bridge\BridgeConstantsInterface;
use EonX\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class EasyRepositoryExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(PaginatedObjectRepositoryInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_PAGINATED_REPOSITORY);
    }
}
