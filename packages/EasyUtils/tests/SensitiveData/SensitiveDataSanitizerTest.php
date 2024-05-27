<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\SensitiveData;

use EonX\EasyUtils\CreditCard\CreditCardNumberValidator;
use EonX\EasyUtils\SensitiveData\ObjectTransformers\DefaultObjectTransformer;
use EonX\EasyUtils\SensitiveData\ObjectTransformers\ThrowableObjectTransformer;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizer;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\SensitiveData\StringSanitizers\AuthorizationStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\JsonStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\UrlStringSanitizer;
use EonX\EasyUtils\Tests\AbstractSensitiveDataSanitizerTestCase;

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
            new JsonStringSanitizer(),
            new AuthorizationStringSanitizer(),
            new CreditCardNumberStringSanitizer(new CreditCardNumberValidator()),
        ];

        return new SensitiveDataSanitizer($keysToMask ?? [], $maskPattern, $objectTransformers, $stringSanitizers);
    }
}
