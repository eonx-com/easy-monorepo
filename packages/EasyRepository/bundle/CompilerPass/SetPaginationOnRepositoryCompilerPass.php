<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Bundle\CompilerPass;

use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyRepository\Bundle\Enum\ConfigTag;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SetPaginationOnRepositoryCompilerPass implements CompilerPassInterface
{
    private const string PAGINATION_SETTER = 'setPagination';

    /**
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        // Works only if package installed and bundle registered
        if (\interface_exists(PaginationInterface::class) === false
            || $container->hasDefinition(PaginationInterface::class) === false) {
            return;
        }

        // Find all paginated repo, if they have setPagination method, then add the method call
        $paginatedRepoDefs = $container->findTaggedServiceIds(ConfigTag::PaginatedRepository->value);
        $paginationRef = new Reference(PaginationInterface::class);

        foreach ($paginatedRepoDefs as $id => $tags) {
            $repoDef = $container->getDefinition($id);
            $reflection = $container->getReflectionClass($repoDef->getClass());

            if ($reflection === null || $reflection->hasMethod(self::PAGINATION_SETTER) === false) {
                continue;
            }

            $repoDef->addMethodCall(self::PAGINATION_SETTER, [$paginationRef]);
        }
    }
}
