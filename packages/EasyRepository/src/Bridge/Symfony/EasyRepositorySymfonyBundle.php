<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Bridge\Symfony;

use EonX\EasyRepository\Bridge\BridgeConstantsInterface;
use EonX\EasyRepository\Bridge\Symfony\DependencyInjection\Compiler\SetPaginationOnRepositoryPass;
use EonX\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRepositorySymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_repository';

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetPaginationOnRepositoryPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(PaginatedObjectRepositoryInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_PAGINATED_REPOSITORY);
    }
}
