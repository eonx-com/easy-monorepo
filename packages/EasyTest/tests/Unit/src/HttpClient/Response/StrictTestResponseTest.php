<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\HttpClient\Response;

use EonX\EasyTest\HttpClient\Response\StrictTestResponse;
use EonX\EasyTest\Tests\Fixture\Enum\DummyEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class StrictTestResponseTest extends TestCase
{
    /**
     * @see testItSucceedsWithUnsortedQuery
     */
    public static function provideQueryData(): iterable
    {
        yield 'Data only in query' => [
            'arrangedQuery' => [
                'bar' => 'baz',
                'foo' => 'bar',
            ],
            'arrangedUrl' => 'https://example.com',
            'url' => 'https://example.com?foo=bar&bar=baz',
        ];

        yield 'Data in query and in url' => [
            'arrangedQuery' => [
                'bar' => 'baz',
                'foo' => 'bar',
            ],
            'arrangedUrl' => 'https://example.com?gaz=qux&haz=quux',
            'url' => 'https://example.com?gaz=qux&foo=bar&haz=quux&bar=baz',
        ];

        yield 'Data only in url' => [
            'arrangedQuery' => [],
            'arrangedUrl' => 'https://example.com?bar=baz&foo=bar',
            'url' => 'https://example.com?foo=bar&bar=baz',
        ];

        yield 'Array in query' => [
            'arrangedQuery' => [
                'tags' => [
                    'bar',
                    'foo',
                ],
            ],
            'arrangedUrl' => 'https://example.com',
            'url' => 'https://example.com?tags[0]=foo&tags[1]=bar',
        ];
    }

    public function testItSucceedsWithNotStringData(): void
    {
        $uuid = Uuid::v6();
        $enum = DummyEnum::SomeCase;
        $sut = new StrictTestResponse(
            method: 'POST',
            url: 'https://example.com',
            json: [
                'enum' => $enum,
                'uuid' => $uuid,
            ],
        );

        $mockResponse = $sut(
            method: 'POST',
            url: 'https://example.com',
            options: [
                'normalized_headers' => [],
                'body' => \json_encode([
                    'enum' => $enum,
                    'uuid' => $uuid,
                ]),
            ]
        );

        self::assertSame(200, $mockResponse->getInfo()['http_code']);
    }

    #[DataProvider('provideQueryData')]
    public function testItSucceedsWithUnsortedQuery(array $arrangedQuery, string $arrangedUrl, string $url): void
    {
        $sut = new StrictTestResponse(
            method: 'GET',
            url: $arrangedUrl,
            query: $arrangedQuery,
        );

        $mockResponse = $sut(
            method: 'GET',
            url: $url,
            options: [
                'normalized_headers' => [],
            ]
        );

        self::assertSame(200, $mockResponse->getInfo()['http_code']);
    }

    public function testItSucceedsWithUnsortedRequestData(): void
    {
        $sut = new StrictTestResponse(
            method: 'POST',
            url: 'https://example.com',
            json: [
                'foo' => 'bar',
                'bar' => 'baz',
                'tags' => ['foo', 'bar'],
            ],
        );

        $mockResponse = $sut(
            method: 'POST',
            url: 'https://example.com',
            options: [
                'normalized_headers' => [],
                'body' => \json_encode([
                    'bar' => 'baz',
                    'foo' => 'bar',
                    'tags' => ['foo', 'bar'],
                ]),
            ]
        );

        self::assertSame(200, $mockResponse->getInfo()['http_code']);
    }
}
