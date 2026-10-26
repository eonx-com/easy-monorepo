<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Laravel\SensitiveData\Sanitizer;

use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\Unit\AbstractSensitiveDataSanitizerTestCase;
use EonX\EasyUtils\Tests\Unit\Laravel\LaravelTestCaseTrait;

final class SensitiveDataSanitizerTest extends AbstractSensitiveDataSanitizerTestCase
{
    use LaravelTestCaseTrait;

    public function testSanitizeSucceedsWithDefaultKeysToMask(): void
    {
        $sanitizer = $this->getSanitizer('*REDACTED*');

        $result = $sanitizer->sanitize([
            'cookie' => 'some-value',
            'name' => 'some-value',
            'proxy-authorization' => 'some-value',
            'refresh_token' => 'some-value',
            'refreshToken' => 'some-value',
            'secret' => 'some-value',
            'secret_key' => 'some-value',
            'secretKey' => 'some-value',
            'set-cookie' => 'some-value',
            'x-webhook-signature' => 'some-value',
        ]);

        self::assertSame(
            [
                'cookie' => '*REDACTED*',
                'name' => 'some-value',
                'proxy-authorization' => '*REDACTED*',
                'refresh_token' => '*REDACTED*',
                'refreshToken' => '*REDACTED*',
                'secret' => '*REDACTED*',
                'secret_key' => '*REDACTED*',
                'secretKey' => '*REDACTED*',
                'set-cookie' => '*REDACTED*',
                'x-webhook-signature' => '*REDACTED*',
            ],
            $result
        );
    }

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
