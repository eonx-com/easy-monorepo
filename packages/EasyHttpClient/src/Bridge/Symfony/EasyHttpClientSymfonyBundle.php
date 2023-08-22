<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony;

use Bugsnag\Client;
use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler\DecorateDefaultClientPass;
use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler\DecorateEasyWebhookClientPass;
use EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler\DecorateMessengerSqsClientPass;
use EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyHttpClientSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_http_client';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DecorateDefaultClientPass())
            ->addCompilerPass(new DecorateEasyWebhookClientPass())
            ->addCompilerPass(new DecorateMessengerSqsClientPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(RequestDataModifierInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_REQUEST_DATA_MODIFIER);

        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_DECORATE_DEFAULT_CLIENT,
                $config['decorate_default_client'] ?? false
            );

        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_DECORATE_EASY_WEBHOOK_CLIENT,
                $config['decorate_easy_webhook_client'] ?? false
            );

        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_DECORATE_MESSENGER_SQS_CLIENT,
                $config['decorate_messenger_sqs_client'] ?? false
            );

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_MODIFIERS_ENABLED, $config['modifiers']['enabled'] ?? true);

        $modifiersWhitelist = $config['modifiers']['whitelist'] ?? [null];
        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_MODIFIERS_WHITELIST,
                \count($modifiersWhitelist) === 1 && ($modifiersWhitelist[0] === null) ? null : $modifiersWhitelist
            );

        $container->import(__DIR__ . '/Resources/config/http_client.php');

        if (($config['easy_bugsnag_enabled'] ?? true) && \class_exists(Client::class)) {
            $container->import(__DIR__ . '/Resources/config/easy_bugsnag.php');
        }

        if (($config['psr_logger_enabled'] ?? true) && \interface_exists(LoggerInterface::class)) {
            $container->import(__DIR__ . '/Resources/config/psr_logger.php');
        }
    }
}
