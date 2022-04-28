<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyRepository\Bridge\BridgeConstantsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SetPaginationOnRepositoryPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const PAGINATION_SETTER = 'setPagination';

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
        $paginatedRepoDefs = $container->findTaggedServiceIds(BridgeConstantsInterface::TAG_PAGINATED_REPOSITORY);
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
