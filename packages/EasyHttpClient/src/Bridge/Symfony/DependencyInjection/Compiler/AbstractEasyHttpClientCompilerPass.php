<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
        $def = (new Definition(WithEventsHttpClient::class))
            ->setAutowired(true)
            ->setAutoconfigured(true)
            // lower priority than MockHttpClient (-10)
            ->setDecoratedService(id: $decorated, priority: -11);

        $container->setDefinition($definitionId, $def);
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
