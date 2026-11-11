<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\SensitiveData\Sanitizer;

use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidator;
use EonX\EasyUtils\SensitiveData\Sanitizer\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

final class CreditCardNumberStringSanitizerTest extends AbstractUnitTestCase
{
    /**
     * @see testSanitizeStringMasksCardAfterLongDigitRun
     */
    public static function provideLongDigitRuns(): iterable
    {
        yield 'digits' => [\str_repeat('1', 65536) . ' '];
        yield 'space-separated digits' => [\str_repeat('1 ', 65536)];
        yield 'space-separated IP addresses' => [\str_repeat('10.0.0.1 ', 8192)];
    }

    /**
     * Patterns compiled earlier in the process keep their JIT code and can still match under these limits,
     * so the test runs in its own process where no pattern of the sanitizer is compiled yet
     */
    #[RunInSeparateProcess]
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

    #[DataProvider('provideLongDigitRuns')]
    public function testSanitizeStringMasksCardAfterLongDigitRun(string $digitRun): void
    {
        $sanitizer = new CreditCardNumberStringSanitizer(new CreditCardNumberValidator());

        $result = $sanitizer->sanitizeString($digitRun . '4005550000000001 tail', '*REDACTED*', []);

        self::assertSame($digitRun . '400555*REDACTED*0001 tail', $result);
    }
}
