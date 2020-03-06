<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Helpers;

use EonX\EasyAsync\Helpers\JsonHelper;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class JsonHelperTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerDecode(): iterable
    {
        yield 'null' => [null, null];

        yield 'simple' => [
            '{"key":"value"}',
            ['key' => 'value'],
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public function providerEncode(): iterable
    {
        yield 'null' => [null, null];

        yield 'simple' => [
            ['key' => 'value'],
            '{"key":"value"}',
        ];
    }

    /**
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
     * @param null|mixed[] $data
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
