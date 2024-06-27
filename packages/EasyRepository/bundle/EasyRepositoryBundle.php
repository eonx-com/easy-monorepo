<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Bundle;

use EonX\EasyRepository\Bundle\CompilerPass\SetPaginationOnRepositoryCompilerPass;
use EonX\EasyRepository\Bundle\Enum\ConfigTag;
use EonX\EasyRepository\Repository\PaginatedObjectRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRepositoryBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetPaginationOnRepositoryCompilerPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(PaginatedObjectRepositoryInterface::class)
            ->addTag(ConfigTag::PaginatedRepository->value);
    }
}
