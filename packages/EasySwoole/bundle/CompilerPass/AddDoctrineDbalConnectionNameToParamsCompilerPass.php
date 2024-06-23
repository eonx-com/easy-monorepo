<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle\CompilerPass;

use EonX\EasySwoole\Doctrine\Enum\CoroutinePdoDriverOption;
use EonX\EasySwoole\Doctrine\Factory\CoroutineConnectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function Symfony\Component\String\u;

final class AddDoctrineDbalConnectionNameToParamsCompilerPass implements CompilerPassInterface
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

                // Support primary + read replica feature
                if (\is_array($params['primary'] ?? null)) {
                    $params['primary'] = $this->setPoolName($params['primary'], $matches[1]);
                }

                if (\is_array($params['replica'] ?? null)) {
                    foreach (\array_keys($params['replica']) as $replicaName) {
                        $params['replica'][$replicaName] = $this->setPoolName(
                            $params['replica'][$replicaName],
                            \sprintf('%s_replica_%s', $matches[1], $replicaName)
                        );
                    }
                }

                // Support simple connection
                if (\is_array($params['primary'] ?? null) === false) {
                    $params = $this->setPoolName($params, $matches[1]);
                }

                $definition->replaceArgument(0, $params);
            }
        }
    }

    private function setPoolName(array $params, string $poolName): array
    {
        $params['driverOptions'] = \array_merge([
            CoroutinePdoDriverOption::PoolName->value => $poolName,
        ], $params['driverOptions'] ?? []);

        return $params;
    }
}
