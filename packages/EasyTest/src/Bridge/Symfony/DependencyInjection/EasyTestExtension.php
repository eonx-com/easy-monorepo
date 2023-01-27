<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\Symfony\DependencyInjection;

use EonX\EasyTest\Bridge\BridgeConstantsInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class EasyTestExtension extends Extension
{
    /**
     * @var string[]
     */
    private const CONFIGS_TO_PARAMS = [
        'enable_message_logger_listener_stub' => BridgeConstantsInterface::PARAM_MAILER_MESSAGE_LOGGER_LISTENER_STUB_ENABLED,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach (self::CONFIGS_TO_PARAMS as $name => $param) {
            $container->setParameter($param, $config[$name]);
        }
    }
}
