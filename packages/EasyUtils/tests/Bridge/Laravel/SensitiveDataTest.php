<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Laravel;

use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\AbstractSensitiveDataSanitizerTestCase;

final class SensitiveDataTest extends AbstractSensitiveDataSanitizerTestCase
{
    use LaravelTestCaseTrait;

    protected function getSanitizer(?array $keysToMask = null): SensitiveDataSanitizerInterface
    {
        $app = $this->getApplication([
            'easy-utils' => [
                'sensitive_data' => [
                    'keys_to_mask' => $keysToMask,
                ],
            ],
        ]);

        return $app->make(SensitiveDataSanitizerInterface::class);
    }
}
