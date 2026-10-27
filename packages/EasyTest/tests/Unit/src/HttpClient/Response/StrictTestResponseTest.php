<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\HttpClient\Response;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use EonX\EasyTest\HttpClient\Response\StrictTestResponse;
use EonX\EasyTest\Tests\Fixture\Enum\DummyEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class StrictTestResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        TestResponseFactory::reset();

        parent::tearDown();
    }

    /**
     * @see testItFailsWhenRequestDataIsExpectedButRequestHasNoBody
     */
    public static function provideExpectedRequestDataWithoutRequestBody(): iterable
    {
        yield 'No body with json arranged' => [
            'arrangedBody' => null,
            'arrangedJson' => [
                'foo' => 'bar',
            ],
            'options' => [
                'normalized_headers' => [],
            ],
        ];

        yield 'No body with body arranged' => [
            'arrangedBody' => [
                'foo' => 'bar',
            ],
            'arrangedJson' => null,
            'options' => [
                'normalized_headers' => [],
            ],
        ];

        yield 'Empty body with json arranged' => [
            'arrangedBody' => null,
            'arrangedJson' => [
                'foo' => 'bar',
            ],
            'options' => [
                'normalized_headers' => [],
                'body' => '',
            ],
        ];

        yield 'Empty body with body arranged' => [
            'arrangedBody' => [
                'foo' => 'bar',
            ],
            'arrangedJson' => null,
            'options' => [
                'normalized_headers' => [],
                'body' => '',
            ],
        ];
    }

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

    /**
     * @see testItSucceedsWithoutRequestData
     */
    public static function provideRequestOptionsWithoutBody(): iterable
    {
        yield 'No body with nothing arranged' => [
            'arrangedBody' => null,
            'arrangedJson' => null,
            'options' => [
                'normalized_headers' => [],
            ],
        ];

        yield 'Empty body with nothing arranged' => [
            'arrangedBody' => null,
            'arrangedJson' => null,
            'options' => [
                'normalized_headers' => [],
                'body' => '',
            ],
        ];

        yield 'No body with empty body arranged' => [
            'arrangedBody' => [],
            'arrangedJson' => null,
            'options' => [
                'normalized_headers' => [],
            ],
        ];

        yield 'Empty body with empty body arranged' => [
            'arrangedBody' => [],
            'arrangedJson' => null,
            'options' => [
                'normalized_headers' => [],
                'body' => '',
            ],
        ];

        yield 'No body with empty json arranged' => [
            'arrangedBody' => null,
            'arrangedJson' => [],
            'options' => [
                'normalized_headers' => [],
            ],
        ];

        yield 'Empty body with empty json arranged' => [
            'arrangedBody' => null,
            'arrangedJson' => [],
            'options' => [
                'normalized_headers' => [],
                'body' => '',
            ],
        ];
    }

    #[DataProvider('provideExpectedRequestDataWithoutRequestBody')]
    public function testItFailsWhenRequestDataIsExpectedButRequestHasNoBody(
        ?array $arrangedBody,
        ?array $arrangedJson,
        array $options,
    ): void {
        $sut = new StrictTestResponse(
            method: 'POST',
            url: 'https://example.com',
            body: $arrangedBody,
            json: $arrangedJson,
        );
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Request to https://example.com has no body, but request data is expected');

        try {
            $sut(
                method: 'POST',
                url: 'https://example.com',
                options: $options
            );
        } finally {
            self::assertInstanceOf(ExpectationFailedException::class, TestResponseFactory::getException());
        }
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
                'some-data' => [
                    'some-data' => 'some-value',
                ],
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
                    'some-data' => [
                        'some-data' => 'some-value',
                    ],
                ]),
            ]
        );

        self::assertSame(200, $mockResponse->getStatusCode());
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

        self::assertSame(200, $mockResponse->getStatusCode());
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

        self::assertSame(200, $mockResponse->getStatusCode());
    }

    #[DataProvider('provideRequestOptionsWithoutBody')]
    public function testItSucceedsWithoutRequestData(?array $arrangedBody, ?array $arrangedJson, array $options): void
    {
        $sut = new StrictTestResponse(
            method: 'POST',
            url: 'https://example.com',
            body: $arrangedBody,
            json: $arrangedJson,
        );

        $mockResponse = $sut(
            method: 'POST',
            url: 'https://example.com',
            options: $options
        );

        self::assertSame(200, $mockResponse->getStatusCode());
    }
}
