<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Bundle;

use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRequestResponseLoggingBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if ($config['enabled'] === false) {
            return;
        }

        if ($this->isBundleEnabled('EasyRequestIdBundle', $builder) === false) {
            throw new LogicException('EasyRequestIdBundle is not enabled.');
        }

        $container->parameters()
            ->set(ConfigParam::DoNotLogPaths->value, $config['do_not_log_paths'])
            ->set(ConfigParam::LogRequestBody->value, $config['log_request_body'])
            ->set(ConfigParam::LogResponseBody->value, $config['log_response_body'])
            ->set(ConfigParam::MaxBodySize->value, $config['max_body_size'])
            ->set(ConfigParam::MaxRawBodySize->value, $config['max_raw_body_size']);

        $container->import('config/services.php');
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }
}
