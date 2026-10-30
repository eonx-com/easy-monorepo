<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\CompilerPass;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

final class ValidateSensitiveDataSanitizerCompilerPass implements CompilerPassInterface
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
                'Sensitive data sanitization of logs ("easy_logging.sensitive_data_sanitizer", enabled by default) '
                . 'requires the eonx-com/easy-utils bundle to be registered and its sensitive data sanitizer not to be '
                . 'disabled. Register EasyUtilsBundle, or disable "easy_logging.sensitive_data_sanitizer" explicitly.'
            );
        }
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(ConfigParam::SensitiveDataSanitizerEnabled->value)
            && (bool)$container->getParameter(ConfigParam::SensitiveDataSanitizerEnabled->value);
    }
}
