<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\SensitiveData\Sanitizer;

use EonX\EasyUtils\SensitiveData\Sanitizer\UrlStringSanitizer;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

final class UrlStringSanitizerTest extends AbstractUnitTestCase
{
    /**
     * Patterns compiled earlier in the process keep their JIT code and can still match under these limits,
     * so the test runs in its own process where no pattern of the sanitizer is compiled yet
     */
    #[RunInSeparateProcess]
    public function testSanitizeStringKeepsStringWhenPcreFails(): void
    {
        $sanitizer = new UrlStringSanitizer();
        $string = 'log https://user:secret@example.com/token/abc?api_key=secret tail';
        $backtrackLimit = (string)\ini_get('pcre.backtrack_limit');
        $jit = (string)\ini_get('pcre.jit');
        // Without JIT, a backtrack limit of 1 makes every preg_* call of the sanitizer fail
        \ini_set('pcre.jit', '0');
        \ini_set('pcre.backtrack_limit', '1');

        try {
            $result = $sanitizer->sanitizeString($string, '*REDACTED*', ['api_key', 'token']);
        } finally {
            \ini_set('pcre.backtrack_limit', $backtrackLimit);
            \ini_set('pcre.jit', $jit);
        }

        self::assertSame($string, $result);
    }

    public function testSanitizeStringMasksKeyWithLongSeparatorRun(): void
    {
        $sanitizer = new UrlStringSanitizer();
        $key = 'api' . \str_repeat('_', 65536) . 'key';

        $result = $sanitizer->sanitizeString($key . '=secret&keep=1', '*REDACTED*', ['api_key']);

        self::assertSame($key . '=*REDACTED*&keep=1', $result);
    }
}
