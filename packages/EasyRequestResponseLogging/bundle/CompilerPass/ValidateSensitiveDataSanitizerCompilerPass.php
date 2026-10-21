<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Bundle\CompilerPass;

use EonX\EasyRequestResponseLogging\Common\Subscriber\RequestResponseLoggingSubscriber;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

final class ValidateSensitiveDataSanitizerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(RequestResponseLoggingSubscriber::class) === false) {
            return;
        }

        if ($container->has(SensitiveDataSanitizerInterface::class) === false) {
            throw new LogicException(
                'To use request/response logging, the EasyUtilsBundle must be enabled '
                . 'and its sensitive data sanitizer must not be disabled.'
            );
        }
    }
}
