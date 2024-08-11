<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bundle\CompilerPass;

use EonX\EasyBatch\Bundle\Enum\ConfigServiceId;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SetEncryptorOnBatchItemTransformerCompilerPass implements CompilerPassInterface
{
    private const ENCRYPTOR_SETTER = 'setEncryptor';

    public function process(ContainerBuilder $container): void
    {
        // Works only if eonx-com/easy-encryption is installed
        if (\interface_exists(EncryptorInterface::class) === false) {
            return;
        }

        $batchItemTransformerDef = $container->getDefinition(ConfigServiceId::BatchItemTransformer->value);
        $batchItemTransformReflection = $container->getReflectionClass($batchItemTransformerDef->getClass());

        // Handles custom batchItem transfer implementation without encryptor setter
        if ($batchItemTransformReflection === null
            || $batchItemTransformReflection->hasMethod(self::ENCRYPTOR_SETTER) === false
            || $batchItemTransformReflection->getMethod(self::ENCRYPTOR_SETTER)->isPublic() === false) {
            return;
        }

        $batchItemTransformerDef->addMethodCall(self::ENCRYPTOR_SETTER, [new Reference(EncryptorInterface::class)]);
    }
}
