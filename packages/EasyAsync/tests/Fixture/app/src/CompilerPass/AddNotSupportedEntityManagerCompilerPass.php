<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Fixture\App\CompilerPass;

use EonX\EasyAsync\Tests\Fixture\App\ObjectManager\NotSupportedObjectManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AddNotSupportedEntityManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->removeDefinition('doctrine.orm.proxy_cache_warmer');

        /** @var array $entityManagers */
        $entityManagers = $container->getParameter('doctrine.entity_managers');

        $entityManagers['not_supported'] = NotSupportedObjectManager::class;

        $container->setParameter('doctrine.entity_managers', $entityManagers);
    }
}
