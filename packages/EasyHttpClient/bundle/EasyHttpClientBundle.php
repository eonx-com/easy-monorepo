<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle;

use Bugsnag\Client;
use EonX\EasyHttpClient\Bundle\CompilerPass\DecorateDefaultClientCompilerPass;
use EonX\EasyHttpClient\Bundle\CompilerPass\DecorateEasyWebhookClientCompilerPass;
use EonX\EasyHttpClient\Bundle\CompilerPass\DecorateMessengerSqsClientCompilerPass;
use EonX\EasyHttpClient\Bundle\Enum\ConfigParam;
use EonX\EasyHttpClient\Bundle\Enum\ConfigTag;
use EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $container
            ->parameters()
            ->set(
                ConfigParam::DecorateDefaultClient->value,
                $config['decorate_default_client'] ?? false
            );

        $container
            ->parameters()
            ->set(
                ConfigParam::DecorateEasyWebhookClient->value,
                $config['decorate_easy_webhook_client'] ?? false
            );

        $container
            ->parameters()
            ->set(
                ConfigParam::DecorateMessengerSqsClient->value,
                $config['decorate_messenger_sqs_client'] ?? false
            );

        $container
            ->parameters()
            ->set(ConfigParam::ModifiersEnabled->value, $config['modifiers']['enabled'] ?? true);

        $modifiersWhitelist = $config['modifiers']['whitelist'] ?? [null];
        $container
            ->parameters()
            ->set(
                ConfigParam::ModifiersWhitelist->value,
                \count($modifiersWhitelist) === 1 && ($modifiersWhitelist[0] === null) ? null : $modifiersWhitelist
            );

        $container->import('config/http_client.php');

        if (($config['easy_bugsnag_enabled'] ?? true) && \class_exists(Client::class)) {
            $container->import('config/easy_bugsnag.php');
        }

        if (($config['psr_logger_enabled'] ?? true) && \interface_exists(LoggerInterface::class)) {
            $container->import('config/psr_logger.php');
        }
    }
}
