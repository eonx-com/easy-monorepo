<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Laravel;

use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\AbstractSensitiveDataSanitizerTestCase;

final class SensitiveDataTest extends AbstractSensitiveDataSanitizerTestCase
{
    use LaravelTestCaseTrait;

    protected function getSanitizer(string $maskPattern, ?array $keysToMask = null): SensitiveDataSanitizerInterface
    {
        $app = $this->getApplication([
            'easy-utils' => [
                'sensitive_data' => [
                    'mask_pattern' => $maskPattern,
                    'keys_to_mask' => $keysToMask ?? [],
                ],
            ],
        ]);

        return $app->make(SensitiveDataSanitizerInterface::class);
    }
}
