<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bundle\CompilerPass;

use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class AwsCloudHsmCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (
            $container->hasParameter(ConfigParam::AwsCloudHsmEnabled->value) === false
            || $container->getParameter(ConfigParam::AwsCloudHsmEnabled->value) === false
        ) {
            return;
        }

        $definition = $container->getDefinition(StringEncryptorInterface::class);

        $definition->replaceArgument(
            0,
            $container->getDefinition(AwsCloudHsmEncryptorInterface::class)
        );
    }
}
