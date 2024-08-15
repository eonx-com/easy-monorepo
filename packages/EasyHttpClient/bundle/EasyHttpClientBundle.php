<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle;

use EonX\EasyHttpClient\Bundle\CompilerPass\DecorateDefaultClientCompilerPass;
use EonX\EasyHttpClient\Bundle\CompilerPass\DecorateEasyWebhookClientCompilerPass;
use EonX\EasyHttpClient\Bundle\CompilerPass\DecorateMessengerSqsClientCompilerPass;
use EonX\EasyHttpClient\Bundle\Enum\ConfigParam;
use EonX\EasyHttpClient\Bundle\Enum\ConfigTag;
use EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyHttpClientBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DecorateDefaultClientCompilerPass())
            ->addCompilerPass(new DecorateEasyWebhookClientCompilerPass())
            ->addCompilerPass(new DecorateMessengerSqsClientCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(RequestDataModifierInterface::class)
            ->addTag(ConfigTag::RequestDataModifier->value);

        $container->parameters()
            ->set(ConfigParam::DecorateDefaultClient->value, $config['decorate_default_client'])
            ->set(ConfigParam::DecorateEasyWebhookClient->value, $config['decorate_easy_webhook_client'])
            ->set(ConfigParam::DecorateMessengerSqsClient->value, $config['decorate_messenger_sqs_client'])
            ->set(ConfigParam::ModifiersEnabled->value, $config['modifiers']['enabled'])
            ->set(ConfigParam::ModifiersWhitelist->value, $config['modifiers']['whitelist']);

        $container->import('config/http_client.php');

        $this->registerEasyBugsnagConfiguration($config, $container, $builder);
        $this->registerPsrLoggerConfiguration($config, $container, $builder);
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function registerEasyBugsnagConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['easy_bugsnag']['enabled'] === false) {
            return;
        }

        if ($this->isBundleEnabled('EasyBugsnagBundle', $builder) === false) {
            throw new LogicException('EasyBugsnagBundle is not enabled.');
        }

        $container->import('config/easy_bugsnag.php');
    }

    private function registerPsrLoggerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['psr_logger']['enabled'] === false) {
            return;
        }

        if (\interface_exists(LoggerInterface::class) === false) {
            throw new LogicException(LoggerInterface::class . ' is not available.');
        }

        $container->import('config/psr_logger.php');
    }
}
