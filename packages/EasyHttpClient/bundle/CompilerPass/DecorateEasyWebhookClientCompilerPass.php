<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle\CompilerPass;

use EonX\EasyHttpClient\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId as EasyWebhookConfigServiceId;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DecorateEasyWebhookClientCompilerPass extends AbstractEasyHttpClientCompilerPass
{
    private const DECORATION_SERVICE_ID = 'easy_http_client.decorate_easy_webhook';

    protected function doProcess(ContainerBuilder $container): void
    {
        // Apply only if enabled, easy-webhook is installed and client definition exists
        if (\enum_exists(EasyWebhookConfigServiceId::class) === false
            || $container->has(EasyWebhookConfigServiceId::HttpClient->value) === false) {
            return;
        }

        $this->decorateHttpClient(
            $container,
            EasyWebhookConfigServiceId::HttpClient->value,
            self::DECORATION_SERVICE_ID
        );
    }

    protected function getEnableParamName(): string
    {
        return ConfigParam::DecorateEasyWebhookClient->value;
    }
}
