<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Laravel\SensitiveData\Sanitizer;

use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\Unit\AbstractSensitiveDataSanitizerTestCase;
use EonX\EasyUtils\Tests\Unit\Laravel\LaravelTestCaseTrait;

final class SensitiveDataSanitizerTest extends AbstractSensitiveDataSanitizerTestCase
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
