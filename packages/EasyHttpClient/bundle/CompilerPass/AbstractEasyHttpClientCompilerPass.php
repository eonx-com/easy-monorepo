<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle\CompilerPass;

use EonX\EasyHttpClient\Bundle\Enum\ConfigServiceId;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractEasyHttpClientCompilerPass implements CompilerPassInterface
{
    protected const DEFAULT_CLIENT_ID = 'http_client.transport';

    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false) {
            return;
        }

        $this->doProcess($container);
    }

    abstract protected function doProcess(ContainerBuilder $container): void;

    abstract protected function getEnableParamName(): string;

    protected function decorateHttpClient(ContainerBuilder $container, string $decorated, string $definitionId): void
    {
        $definition = clone $container->getDefinition(ConfigServiceId::HttpClient->value);

        // Lower priority than MockHttpClient (-10)
        $definition->setDecoratedService(id: $decorated, priority: -11);

        $container->setDefinition($definitionId, $definition);
    }

    protected function hasDefaultClient(ContainerBuilder $container): bool
    {
        return $container->has(self::DEFAULT_CLIENT_ID);
    }

    protected function isEnabled(ContainerBuilder $container): bool
    {
        $param = $this->getEnableParamName();

        return $container->hasParameter($param) && $container->getParameter($param);
    }
}
