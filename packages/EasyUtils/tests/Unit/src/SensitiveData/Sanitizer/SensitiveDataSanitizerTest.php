<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\SensitiveData\Sanitizer;

use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidator;
use EonX\EasyUtils\SensitiveData\Sanitizer\AuthorizationStringSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\SensitiveData\Sanitizer\UrlStringSanitizer;
use EonX\EasyUtils\SensitiveData\Transformer\DefaultObjectTransformer;
use EonX\EasyUtils\SensitiveData\Transformer\ThrowableObjectTransformer;
use EonX\EasyUtils\Tests\Unit\AbstractSensitiveDataSanitizerTestCase;

final class SensitiveDataSanitizerTest extends AbstractSensitiveDataSanitizerTestCase
{
    /**
     * @param string[]|null $keysToMask
     */
    protected function getSanitizer(string $maskPattern, ?array $keysToMask = null): SensitiveDataSanitizerInterface
    {
        $objectTransformers = [
            new ThrowableObjectTransformer(),
            new DefaultObjectTransformer(),
        ];

        $stringSanitizers = [
            new UrlStringSanitizer(),
            new AuthorizationStringSanitizer(),
            new CreditCardNumberStringSanitizer(new CreditCardNumberValidator()),
        ];

        return new SensitiveDataSanitizer($keysToMask ?? [], $maskPattern, $objectTransformers, $stringSanitizers);
    }
}
