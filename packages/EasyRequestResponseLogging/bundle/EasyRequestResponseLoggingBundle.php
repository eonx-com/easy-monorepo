<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Bundle;

use EonX\EasyRequestResponseLogging\Bundle\CompilerPass\ValidateSensitiveDataSanitizerCompilerPass;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRequestResponseLoggingBundle extends AbstractBundle
{
    private bool $useSymfonyMonologBundle = false;

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        // The sanitizer service can only be validated once all extensions are loaded
        $container->addCompilerPass(new ValidateSensitiveDataSanitizerCompilerPass());
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

        if ($this->useSymfonyMonologBundle === false && $this->isBundleEnabled('EasyLoggingBundle', $builder)) {
            $container->import('config/services_easy_logging.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Resolved here because extension configs are only available during the prepend phase: loadExtension()
        // receives a temporary container without them. All prepend hooks run before any loadExtension() call
        $this->useSymfonyMonologBundle = $this->shouldUseSymfonyMonologBundle($builder);
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function shouldUseSymfonyMonologBundle(ContainerBuilder $builder): bool
    {
        $enabled = false;

        foreach ($builder->getExtensionConfig('easy_logging') as $config) {
            if (\array_key_exists('use_symfony_monolog_bundle', $config)) {
                $enabled = $config['use_symfony_monolog_bundle'];
            }
        }

        return (bool)$builder->getParameterBag()
            ->resolveValue($enabled);
    }
}
