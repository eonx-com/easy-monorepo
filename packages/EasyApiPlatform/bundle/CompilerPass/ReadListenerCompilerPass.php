<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class ReadListenerCompilerPass implements CompilerPassInterface
{
    private const DEFINITION_ID_READ_LISTENER = 'api_platform.listener.request.read';

    private const READ_LISTENER_PRIORITY = 5;

    public function process(ContainerBuilder $container): void
    {
        if ($container->has(self::DEFINITION_ID_READ_LISTENER) === false) {
            return;
        }

        $definition = $container->getDefinition(self::DEFINITION_ID_READ_LISTENER);

        $definition->clearTag('kernel.event_listener');
        $definition->addTag('kernel.event_listener', [
            'event' => 'kernel.request',
            'method' => 'onKernelRequest',
            'priority' => self::READ_LISTENER_PRIORITY,
        ]);
    }
}
