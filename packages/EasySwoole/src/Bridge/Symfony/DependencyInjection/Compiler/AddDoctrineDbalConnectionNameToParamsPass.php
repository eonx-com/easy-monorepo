<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasySwoole\Bridge\Doctrine\Coroutine\Enum\CoroutinePdoDriverOption;
use EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO\CoroutineConnectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function Symfony\Component\String\u;

final class AddDoctrineDbalConnectionNameToParamsPass implements CompilerPassInterface
{
    private const CONNECTION_REGEX = '/doctrine.dbal.(.+)_connection/';

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(CoroutineConnectionFactory::class) === false) {
            return;
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            $matches = u($id)
                ->match(self::CONNECTION_REGEX);

            if (\is_string($matches[1] ?? null) && ($matches[0] ?? null) === $id) {
                $params = $definition->getArguments()[0];
                $params['driverOptions'] = \array_merge([
                    CoroutinePdoDriverOption::PoolName->value => $matches[1],
                ], $params['driverOptions'] ?? []);

                $definition->replaceArgument(0, $params);
            }
        }
    }
}
