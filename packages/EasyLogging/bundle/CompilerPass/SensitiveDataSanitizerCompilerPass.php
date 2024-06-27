<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\CompilerPass;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class SensitiveDataSanitizerCompilerPass implements CompilerPassInterface
{
    public const SANITIZER_ID = SensitiveDataSanitizerInterface::class;

    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false) {
            return;
        }

        if (\interface_exists(self::SANITIZER_ID) === false || $container->has(self::SANITIZER_ID) === false) {
            throw new LogicException(
                'To use sensitive data sanitization, the package eonx-com/easy-utils must be installed,
                and its bundle must be enabled'
            );
        }

        $def = (new Definition(SensitiveDataSanitizerProcessor::class, [
            '$sensitiveDataSanitizer' => new Reference(SensitiveDataSanitizerInterface::class),
        ]))
            ->addTag(ConfigTag::ProcessorConfigProvider->value)
            ->setAutoconfigured(true);

        $container->setDefinition(SensitiveDataSanitizerProcessor::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(ConfigParam::SensitiveDataSanitizerEnabled->value)
            && $container->getParameter(ConfigParam::SensitiveDataSanitizerEnabled->value);
    }
}
