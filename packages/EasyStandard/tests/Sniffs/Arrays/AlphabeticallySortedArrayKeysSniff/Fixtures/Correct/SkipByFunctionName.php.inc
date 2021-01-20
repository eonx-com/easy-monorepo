<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff\Fixtures\Correct;

final class TestSomeClass
{
    /**
     * @return mixed[]
     */
    private function provideSomeData(): array
    {
        return [
            'iat' => \time(),
            'iss' => self::TEST_CONST,
            'sub' => $someObject->getValue(),
            SomeFile::TEST_CONST => 'abc',
            self::ANOTHER_TEST_CONST => [
                'fn' => $someObject->getFn(),
                'ln' => $someObject->getLn(),
                'email' => $someObject->getEmail(),
                'phone' => $someObject->getPhone(),
            ],
            'aud' => self::ANOTHER_TEST_CONST,
            'exp' => \time() + 3600,
        ];
    }
}
