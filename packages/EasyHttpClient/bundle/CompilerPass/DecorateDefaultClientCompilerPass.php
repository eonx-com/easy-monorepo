<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle\CompilerPass;

use EonX\EasyHttpClient\Bundle\Enum\ConfigParam;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DecorateDefaultClientCompilerPass extends AbstractEasyHttpClientCompilerPass
{
    private const DECORATION_SERVICE_ID = 'easy_http_client.decorate_default';

    protected function doProcess(ContainerBuilder $container): void
    {
        if ($this->hasDefaultClient($container) === false) {
            return;
        }

        $this->decorateHttpClient($container, self::DEFAULT_CLIENT_ID, self::DECORATION_SERVICE_ID);
    }

    protected function getEnableParamName(): string
    {
        return ConfigParam::DecorateDefaultClient->value;
    }
}
