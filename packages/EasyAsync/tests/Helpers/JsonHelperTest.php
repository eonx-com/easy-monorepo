<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Helpers;

use EonX\EasyAsync\Helpers\JsonHelper;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class JsonHelperTest extends AbstractTestCase
{
    /**
     * DataProvider for testDecode.
     *
     * @return iterable<mixed>
     */
    public function providerDecode(): iterable
    {
        yield 'null' => [null, null];

        yield 'simple' => [
            '{"key":"value"}',
            ['key' => 'value']
        ];
    }

    /**
     * DataProvider for testEncode.
     *
     * @return iterable<mixed>
     */
    public function providerEncode(): iterable
    {
        yield 'null' => [null, null];

        yield 'simple' => [
            ['key' => 'value'],
            '{"key":"value"}'
        ];
    }

    /**
     * Helper should decode given data.
     *
     * @param null|string $data
     * @param null|mixed[] $expected
     *
     * @throws \Nette\Utils\JsonException
     *
     * @dataProvider providerDecode
     */
    public function testDecode(?string $data = null, ?array $expected = null): void
    {
        self::assertEquals($expected, JsonHelper::decode($data));
    }

    /**
     * Helper should encode given data.
     *
     * @param null|mixed[] $data
     * @param null|string $expected
     *
     * @return void
     *
     * @throws \Nette\Utils\JsonException
     *
     * @dataProvider providerEncode
     */
    public function testEncode(?array $data = null, ?string $expected = null): void
    {
        self::assertEquals($expected, JsonHelper::encode($data));
    }
}
