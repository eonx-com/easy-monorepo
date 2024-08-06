<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Bundle\SensitiveData\Sanitizer;

use EonX\EasyUtils\Bundle\Enum\ConfigParam;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\Stub\Kernel\KernelTrait;
use EonX\EasyUtils\Tests\Unit\AbstractSensitiveDataSanitizerTestCase;

final class SensitiveDataSanitizerTest extends AbstractSensitiveDataSanitizerTestCase
{
    use KernelTrait;

    protected function getSanitizer(string $maskPattern, ?array $keysToMask = null): SensitiveDataSanitizerInterface
    {
        $container = $this->getKernel([
            ConfigParam::SensitiveDataKeysToMask->value => $keysToMask ?? [],
            ConfigParam::SensitiveDataMaskPattern->value => $maskPattern,
        ])
            ->getContainer();

        return $container->get(SensitiveDataSanitizerInterface::class);
    }
}
