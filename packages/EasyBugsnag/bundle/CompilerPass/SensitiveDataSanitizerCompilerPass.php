<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bundle\CompilerPass;

use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Bundle\Enum\ConfigTag;
use EonX\EasyBugsnag\Configurator\SensitiveDataSanitizerClientConfigurator;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class SensitiveDataSanitizerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false) {
            return;
        }

        if (
            \interface_exists(SensitiveDataSanitizerInterface::class) === false
            || $container->has(SensitiveDataSanitizerInterface::class) === false
        ) {
            throw new LogicException(
                'To use sensitive data sanitization, the package eonx-com/easy-utils must be installed,
                and its bundle must be enabled'
            );
        }

        $def = (new Definition(SensitiveDataSanitizerClientConfigurator::class, [
            '$sensitiveDataSanitizer' => new Reference(SensitiveDataSanitizerInterface::class),
        ]))
            ->addTag(ConfigTag::ClientConfigurator->value)
            ->setAutoconfigured(true);

        $container->setDefinition(SensitiveDataSanitizerClientConfigurator::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(ConfigParam::SensitiveDataSanitizerEnabled->value)
            && $container->getParameter(ConfigParam::SensitiveDataSanitizerEnabled->value);
    }
}
