<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MakeMessengerReceiversPublicCompilerPass implements CompilerPassInterface
{
    private const RECEIVER_TAG = 'messenger.receiver';

    public function process(ContainerBuilder $container): void
    {
        foreach (\array_keys($container->findTaggedServiceIds(self::RECEIVER_TAG)) as $receiverId) {
            $container
                ->getDefinition($receiverId)
                ->setPublic(true);
        }
    }
}
