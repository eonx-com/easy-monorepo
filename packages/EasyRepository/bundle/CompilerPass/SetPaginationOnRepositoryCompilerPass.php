<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Bundle\CompilerPass;

use EonX\EasyPagination\ValueObject\Pagination;
use EonX\EasyRepository\Bundle\Enum\ConfigTag;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SetPaginationOnRepositoryCompilerPass implements CompilerPassInterface
{
    private const PAGINATION_SETTER = 'setPagination';

    /**
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        // Works only if package installed and bundle registered
        if (\class_exists(Pagination::class) === false
            || $container->hasDefinition(Pagination::class) === false) {
            return;
        }

        // Find all paginated repo, if they have setPagination method, then add the method call
        $paginatedRepoDefs = $container->findTaggedServiceIds(ConfigTag::PaginatedRepository->value);
        $paginationRef = new Reference(Pagination::class);

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
