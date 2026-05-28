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
     * @see testItSucceedsForMaskFirst
     */
    public static function provideMaskFirstData(): iterable
    {
        yield '2 chars, mask 4' => ['value' => '12', 'maskLength' => 4, 'expected' => '**'];

        yield '5 chars, mask 4' => ['value' => '12345', 'maskLength' => 4, 'expected' => '****5'];

        yield '6 chars, mask 4' => ['value' => '123456', 'maskLength' => 4, 'expected' => '****56'];

        yield '7 chars, mask 4' => ['value' => '1234567', 'maskLength' => 4, 'expected' => '****567'];

        yield '8 chars, mask 4' => ['value' => '12345678', 'maskLength' => 4, 'expected' => '****5678'];

        yield '9 chars, mask 4' => ['value' => '123456789', 'maskLength' => 4, 'expected' => '****56789'];

        yield 'custom masking symbol' => [
            'value' => '123456',
            'maskLength' => 4,
            'expected' => 'XXXX56',
            'maskingSymbol' => 'X',
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

    /** @param positive-int $maskLength */
    #[DataProvider('provideMaskFirstData')]
    public function testItSucceedsForMaskFirst(
        string $value,
        int $maskLength,
        string $expected,
        ?string $maskingSymbol = null,
    ): void {
        $result = StringMaskHelper::maskFirst($value, $maskLength, $maskingSymbol);

        self::assertSame($expected, $result);
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
}
