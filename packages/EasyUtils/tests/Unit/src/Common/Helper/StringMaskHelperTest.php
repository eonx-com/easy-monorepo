<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Helper;

use EonX\EasyUtils\Common\Helper\StringMaskHelper;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StringMaskHelperTest extends AbstractUnitTestCase
{
    /**
     * @see testMaskEmailSucceeds
     */
    public static function provideMaskEmailData(): iterable
    {
        yield 'typical email' => ['email' => 'john.doe@example.com', 'expected' => 'jo******@example.com'];

        yield 'short local part' => ['email' => 'ab@example.com', 'expected' => 'a*@example.com'];

        yield 'single char local part' => ['email' => 'a@example.com', 'expected' => '*@example.com'];

        yield 'long local part' => [
            'email' => 'very.long.email.address@domain.org',
            'expected' => 've*********************@domain.org',
        ];

        yield 'custom masking symbol' => [
            'email' => 'john.doe@example.com',
            'expected' => 'jo######@example.com',
            'maskingSymbol' => '#',
        ];
    }

    /**
     * @see testMaskMiddleSucceeds
     */
    public static function provideMaskMiddleData(): iterable
    {
        yield 'short string (length < visible * 2)' => ['value' => '1234', 'visible' => 3, 'expected' => '****'];

        yield 'exact boundary (length = visible * 2)' => [
            'value' => 'abcdef',
            'visible' => 3,
            'expected' => '******',
        ];

        yield 'just above boundary (length = visible * 2 + 1)' => [
            'value' => 'abcdefg',
            'visible' => 3,
            'expected' => 'abc*efg',
        ];

        yield 'typical sha256 hex hash' => [
            'value' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            'visible' => 3,
            'expected' => 'e3b**********************************************************855',
        ];

        yield 'custom masking symbol' => [
            'value' => 'abcdefghij',
            'visible' => 3,
            'expected' => 'abc####hij',
            'maskingSymbol' => '#',
        ];

        yield 'empty string' => [
            'value' => '',
            'visible' => 3,
            'expected' => '',
        ];
    }

    /**
     * @see testMaskPhoneNumberSucceeds
     */
    public static function provideMaskPhoneNumberData(): iterable
    {
        yield 'typical AU mobile' => ['phoneNumber' => '+61412345678', 'expected' => '+*******5678'];

        yield 'nine chars' => ['phoneNumber' => '123456789', 'expected' => '1****6789'];

        yield 'five chars' => ['phoneNumber' => '12345', 'expected' => '12345'];
    }

    #[DataProvider('provideMaskEmailData')]
    public function testMaskEmailSucceeds(string $email, string $expected, ?string $maskingSymbol = null): void
    {
        $result = StringMaskHelper::maskEmail($email, $maskingSymbol);

        self::assertSame($expected, $result);
    }

    /** @param positive-int $visible */
    #[DataProvider('provideMaskMiddleData')]
    public function testMaskMiddleSucceeds(
        string $value,
        int $visible,
        string $expected,
        ?string $maskingSymbol = null,
    ): void {
        $result = StringMaskHelper::maskMiddle($value, $visible, $maskingSymbol);

        self::assertSame($expected, $result);
    }

    #[DataProvider('provideMaskPhoneNumberData')]
    public function testMaskPhoneNumberSucceeds(string $phoneNumber, string $expected): void
    {
        $result = StringMaskHelper::maskPhoneNumber($phoneNumber);

        self::assertSame($expected, $result);
    }
}
