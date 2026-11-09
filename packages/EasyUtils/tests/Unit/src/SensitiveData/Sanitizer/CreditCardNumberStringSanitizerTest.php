<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\SensitiveData\Sanitizer;

use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidator;
use EonX\EasyUtils\SensitiveData\Sanitizer\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;

final class CreditCardNumberStringSanitizerTest extends AbstractUnitTestCase
{
    public function testSanitizeStringKeepsStringWhenPcreFails(): void
    {
        $sanitizer = new CreditCardNumberStringSanitizer(new CreditCardNumberValidator());
        $string = 'job 1f1dc398-654c-6538-9652-0579065566b8 card 4005550000000001 tail';
        $backtrackLimit = (string)\ini_get('pcre.backtrack_limit');
        $jit = (string)\ini_get('pcre.jit');
        // Without JIT, a backtrack limit of 1 makes every preg_* call of the sanitizer fail
        \ini_set('pcre.jit', '0');
        \ini_set('pcre.backtrack_limit', '1');

        try {
            $result = $sanitizer->sanitizeString($string, '*REDACTED*', []);
        } finally {
            \ini_set('pcre.backtrack_limit', $backtrackLimit);
            \ini_set('pcre.jit', $jit);
        }

        self::assertSame($string, $result);
    }
}
