<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle\CompilerPass;

use EonX\EasyHttpClient\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DecorateEasyWebhookClientCompilerPass extends AbstractEasyHttpClientCompilerPass
{
    private const DECORATION_SERVICE_ID = 'easy_http_client.decorate_easy_webhook';

    protected function doProcess(ContainerBuilder $container): void
    {
        // Apply only if enabled, easy-webhook is installed and client definition exists
        if (\interface_exists(EasyWebhookBridgeConstantsInterface::class) === false
            || $container->has(EasyWebhookBridgeConstantsInterface::HTTP_CLIENT) === false) {
            return;
        }

        $this->decorateHttpClient(
            $container,
            EasyWebhookBridgeConstantsInterface::HTTP_CLIENT,
            self::DECORATION_SERVICE_ID
        );
    }

    protected function getEnableParamName(): string
    {
        return ConfigParam::DecorateEasyWebhookClient->value;
    }
}
