<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\SensitiveData;

use EonX\EasyUtils\SensitiveData\ObjectTransformers\DefaultObjectTransformer;
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
    protected function getSanitizer(?array $keysToMask = null): SensitiveDataSanitizerInterface
    {
        $objectTransformers = [
            new DefaultObjectTransformer(),
        ];

        $stringSanitizers = [
            new UrlStringSanitizer(),
            new JsonStringSanitizer(),
            new AuthorizationStringSanitizer(),
            new CreditCardNumberStringSanitizer(),
        ];

        return new SensitiveDataSanitizer($keysToMask, null, $objectTransformers, $stringSanitizers);
    }
}
