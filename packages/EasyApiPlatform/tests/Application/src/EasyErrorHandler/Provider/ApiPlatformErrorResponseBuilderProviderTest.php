<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\EasyErrorHandler\Provider;

use Composer\InstalledVersions;
use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Exception\DummyBException;
use EonX\EasyErrorHandler\Common\Strategy\VerboseStrategyInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

final class ApiPlatformErrorResponseBuilderProviderTest extends AbstractApplicationTestCase
{
    /**
     * @see testBuildErrorResponse
     * @see testBuildExtendedErrorResponse
     */
    public static function provideDataForBuildErrorResponse(): iterable
    {
        yield 'ValidationException' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'printingHouse' => '/printing-houses/1',
                'status' => 'active',
                'weight' => 11,
            ],
            'violations' => [
                'title' => [
                    'This value should not be blank.',
                    'This value should not be null.',
                ],
            ],
            'exceptionMessage' => "title: This value should not be blank.\ntitle: This value should not be null.",
            'version' => null,
        ];

        yield 'ValidationException when enum v3' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'printingHouse' => '/printing-houses/1',
                'status' => 'some-non-existing-status',
                'weight' => 11,
            ],
            'violations' => [
                'status' => [
                    'The value should be a valid choice.',
                ],
            ],
            'exceptionMessage' => 'status: This value should be of type EonX\\EasyApiPlatform\\Tests\\Fixture\\App' .
                '\\EasyErrorHandler\\Enum\\Status.',
            'version' => 3,
        ];

        yield 'ValidationException when enum' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'printingHouse' => '/printing-houses/1',
                'status' => 'some-non-existing-status',
                'weight' => 11,
            ],
            'violations' => [
                'status' => [
                    'The value should be a valid choice.',
                ],
            ],
            'exceptionMessage' => 'status: This value should be of type Status.',
            'version' => 4,
        ];

        yield 'Carbon date with custom Normalizer is empty string' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'someCarbonImmutableDate' => '',
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'This value is not a valid date/time.',
            ],
            'exceptionMessage' => 'Custom message from custom CarbonNormalizer.',
            'version' => null,
        ];

        yield 'Carbon date with custom Normalizer is NULL' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'someCarbonImmutableDate' => null,
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'This value is not a valid date/time.',
            ],
            'exceptionMessage' => 'Custom message from custom CarbonNormalizer.',
            'version' => null,
        ];

        yield 'invalid Carbon date format with custom Normalizer' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'someCarbonImmutableDate' => 'some invalid date',
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'This value is not a valid date/time.',
            ],
            'exceptionMessage' => 'Custom message from custom CarbonNormalizer.',
            'version' => null,
        ];

        yield 'invalid argument type' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'pageCount' => 'some string',
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'pageCount' => [
                    'This value should be of type int.',
                ],
            ],
            'exceptionMessage' => 'pageCount: This value should be of type int.',
            'version' => null,
        ];

        yield 'NULL value' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'pageCount' => null,
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'pageCount' => [
                    'This value should be of type int.',
                ],
            ],
            'exceptionMessage' => 'pageCount: This value should be of type int.',
            'version' => null,
        ];

        yield 'missing constructor argument v3' => [
            'url' => '/books',
            'json' => [],
            'violations' => [
                'description' => [
                    'This value should be of type string.',
                ],
                'printingHouse' => [
                    'This value should be of type /printing-houses IRI.',
                ],
                'weight' => [
                    'This value should be of type int.',
                ],
            ],
            'exceptionMessage' => "description: This value should be of type string.\nweight: This value should" .
                " be of type int.\nprintingHouse: This value should be of type" .
                " EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource\PrintingHouse.",
            'version' => 3,
        ];

        yield 'missing constructor argument' => [
            'url' => '/books',
            'json' => [],
            'violations' => [
                'description' => [
                    'This value should be of type string.',
                ],
                'printingHouse' => [
                    'This value should be of type PrintingHouse.',
                ],
                'weight' => [
                    'This value should be of type int.',
                ],
            ],
            'exceptionMessage' => "description: This value should be of type string.\nweight: This value should" .
                " be of type int.\nprintingHouse: This value should be of type PrintingHouse.",
            'version' => 4,
        ];

        yield 'invalid constructor Enum argument v3' => [
            'url' => '/printing-houses',
            'json' => [
                'name' => 'some name',
                'status' => 'some-non-existing-status',
            ],
            'violations' => [
                'status' => [
                    'The value should be a valid choice.',
                ],
            ],
            'exceptionMessage' => 'status: This value should be of type EonX\\EasyApiPlatform\\Tests\\Fixture\\App' .
                '\\EasyErrorHandler\\Enum\\Status.',
            'version' => 3,
        ];

        yield 'invalid constructor Enum argument' => [
            'url' => '/printing-houses',
            'json' => [
                'name' => 'some name',
                'status' => 'some-non-existing-status',
            ],
            'violations' => [
                'status' => [
                    'The value should be a valid choice.',
                ],
            ],
            'exceptionMessage' => 'status: This value should be of type Status.',
            'version' => 4,
        ];

        yield 'missing constructor argument in DTO' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'author' => [],
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'author.age' => [
                    'This value should be of type int.',
                ],
                'author.name' => [
                    'This value should be of type string.',
                ],
            ],
            'exceptionMessage' => "author.name: This value should be of type string.\nauthor.age:" .
                ' This value should be of type int.',
            'version' => null,
        ];

        yield 'invalid constructor argument type' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'printingHouse' => '/printing-houses/1',
                'weight' => 'some string',
            ],
            'violations' => [
                'weight' => [
                    'This value should be of type int.',
                ],
            ],
            'exceptionMessage' => 'weight: This value should be of type int.',
            'version' => null,
        ];

        yield 'input data is misformatted when invalid argument in DTO' => [
            'url' => '/book-categories-dto',
            'json' => [
                'name' => 'some name',
                'rank' => 'some string',
            ],
            'violations' => [
                'rank' => [
                    'This value should be of type int.',
                ],
            ],
            'exceptionMessage' => 'rank: This value should be of type int.',
            'version' => null,
        ];

        yield 'missing constructor argument in DTO when input DTO' => [
            'url' => '/book-categories-dto-with-constructor',
            'json' => [
                'name' => 'some name',
            ],
            'violations' => [
                'rank' => [
                    'This value should be of type int.',
                ],
            ],
            'exceptionMessage' => 'rank: This value should be of type int.',
            'version' => null,
        ];

        yield 'invalid IRI format v3' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'category' => 'some invalid IRI',
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'category' => [
                    'This value should be of type /book-categories IRI.',
                ],
            ],
            'exceptionMessage' => 'category: This value should be of type' .
                ' EonX\\EasyApiPlatform\\Tests\\Fixture\\App\\EasyErrorHandler\\ApiResource\\Category.',
            'version' => 3,
        ];

        yield 'invalid IRI format' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'category' => 'some invalid IRI',
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'category' => [
                    'Invalid IRI "some invalid IRI".',
                ],
            ],
            'exceptionMessage' => 'category: This value should be of type Category.',
            'version' => 4,
        ];

        yield 'invalid IRI type when constructor parameter v3' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'printingHouse' => 'some string',
            ],
            'violations' => [
                'printingHouse' => [
                    'This value should be of type /printing-houses IRI.',
                ],
            ],
            'exceptionMessage' => 'printingHouse: This value should be of type' .
                ' EonX\\EasyApiPlatform\\Tests\\Fixture\\App\\EasyErrorHandler\\ApiResource\\PrintingHouse.',
            'version' => 3,
        ];

        yield 'invalid IRI type when constructor parameter' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'printingHouse' => 'some string',
            ],
            'violations' => [
                'printingHouse' => [
                    'Invalid IRI "some string".',
                ],
            ],
            'exceptionMessage' => 'printingHouse: This value should be of type PrintingHouse.',
            'version' => 4,
        ];

        yield 'invalid IRI type when input dto' => [
            'url' => '/invoices',
            'json' => [
                'payment' => 'some invalid iri',
            ],
            'violations' => [
                'payment' => [
                    'Invalid IRI "some invalid iri".',
                ],
            ],
            'exceptionMessage' => 'payment: This value should be of type Payment.',
            'version' => 4,
        ];

        yield 'item not found by IRI type when input dto' => [
            'url' => '/invoices',
            'json' => [
                'payment' => '/payments/123',
            ],
            'violations' => [
                'payment' => [
                    'Item not found for "/payments/123".',
                ],
            ],
            'exceptionMessage' => 'payment: This value should be of type Payment.',
            'version' => 4,
        ];

        yield 'different object IRI when constructor parameter' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'printingHouse' => '/publishing-houses/1',
            ],
            'violations' => [
                'printingHouse' => [
                    'This value should be of type /printing-houses IRI.',
                ],
            ],
            'exceptionMessage' => 'EonX\\EasyApiPlatform\\Tests\\Fixture\\App' .
                '\\EasyErrorHandler\\ApiResource\\Book::__construct(): Argument #3 ($printingHouse) must be of type ' .
                'EonX\\EasyApiPlatform\\Tests\\Fixture\\App\\EasyErrorHandler\\ApiResource\\PrintingHouse, ' .
                'EonX\\EasyApiPlatform\\Tests\\Fixture\\App\\EasyErrorHandler\\ApiResource\\PublishingHouse given',
            'version' => null,
        ];

        yield 'date is empty string' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'publishedAt' => '',
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'publishedAt' => [
                    'This value is not a valid date/time.',
                ],
            ],
            'exceptionMessage' => 'publishedAt: This value should be of type string.',
            'version' => null,
        ];

        yield 'date is NULL' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'publishedAt' => null,
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'publishedAt' => [
                    'This value is not a valid date/time.',
                ],
            ],
            'exceptionMessage' => 'publishedAt: This value should be of type string.',
            'version' => null,
        ];

        yield 'invalid date' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'publishedAt' => 'some invalid date',
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'publishedAt' => [
                    'This value should be of type string.',
                ],
            ],
            'exceptionMessage' => 'publishedAt: This value should be of type string.',
            'version' => null,
        ];

        yield 'invalid date format' => [
            'url' => '/books',
            'json' => [
                'availableFrom' => '2024-04-22T01:01:00+11:00',
                'description' => 'some description',
                'printingHouse' => '/printing-houses/1',
                'weight' => 11,
            ],
            'violations' => [
                'availableFrom' => [
                    'This value is not a valid date/time.',
                ],
            ],
            'exceptionMessage' => 'availableFrom: This value should be of type string.',
            'version' => null,
        ];

        yield 'date is empty string, when constructor parameter' => [
            'url' => '/notes',
            'json' => [
                'text' => 'some text',
                'publishedAt' => '',
            ],
            'violations' => [
                'publishedAt' => [
                    'This value is not a valid date/time.',
                ],
            ],
            'exceptionMessage' => 'publishedAt: This value should be of type string.',
            'version' => null,
        ];

        yield 'date is NULL, when constructor parameter' => [
            'url' => '/notes',
            'json' => [
                'text' => 'some text',
                'publishedAt' => null,
            ],
            'violations' => [
                'publishedAt' => [
                    'This value is not a valid date/time.',
                ],
            ],
            'exceptionMessage' => 'publishedAt: This value should be of type string.',
            'version' => null,
        ];

        yield 'invalid date, when constructor parameter' => [
            'url' => '/notes',
            'json' => [
                'text' => 'some text',
                'publishedAt' => 'some invalid date',
            ],
            'violations' => [
                'publishedAt' => [
                    'This value is not a valid date/time.',
                ],
            ],
            'exceptionMessage' => 'publishedAt: This value should be of type string.',
            'version' => null,
        ];

        yield 'invalid date format, when constructor parameter' => [
            'url' => '/notes',
            'json' => [
                'text' => 'some text',
                'publishedAt' => '2024-04-22T01:01:00+11:00',
            ],
            'violations' => [
                'publishedAt' => [
                    'This value is not a valid date/time.',
                ],
            ],
            'exceptionMessage' => 'publishedAt: This value should be of type string.',
            'version' => null,
        ];

        yield 'invalid constructor argument in DTO' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'printingHouse' => '/printing-houses/1',
                'author' => [
                    'age' => 'some string',
                ],
            ],
            'violations' => [
                'author.age' => [
                    'This value should be of type int.',
                ],
                'author.name' => [
                    'This value should be of type string.',
                ],
            ],
            'exceptionMessage' => "author.name: This value should be of type string.\nauthor.age:" .
                ' This value should be of type int.',
            'version' => null,
        ];

        yield 'invalid IRI type' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'category' => 123,
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'category' => [
                    'This value should be an IRI.',
                ],
            ],
            'exceptionMessage' => 'category: This value should be of type array|string.',
            'version' => null,
        ];

        yield 'null IRI when constructor parameter' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'printingHouse' => null,
            ],
            'violations' => [
                'printingHouse' => [
                    'This value should be an IRI.',
                ],
            ],
            'exceptionMessage' => 'printingHouse: This value should be of type array|string.',
            'version' => null,
        ];

        yield 'nested document' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'category' => [
                    'name' => 'some name',
                    'rank' => 10,
                ],
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'category' => [
                    'This value should be an IRI.',
                ],
            ],
            'exceptionMessage' => 'category: This value should be of type array|string.',
            'version' => null,
        ];

        yield 'missing constructor argument with serializedName attribute' => [
            'url' => '/payments',
            'json' => [],
            'violations' => [
                'type' => [
                    'This value should be of type string.',
                ],
            ],
            'exceptionMessage' => 'paymentType: This value should be of type string.',
            'version' => null,
        ];

        yield 'null constructor argument with serializedName attribute' => [
            'url' => '/payments',
            'json' => ['type' => null],
            'violations' => [
                'type' => [
                    'This value should be of type string.',
                ],
            ],
            'exceptionMessage' => 'paymentType: This value should be of type string.',
            'version' => null,
        ];

        yield 'missing constructor argument in input DTO with serializedName attribute' => [
            'url' => '/payments-dto-with-constructor',
            'json' => [],
            'violations' => [
                'type' => [
                    'This value should be of type string.',
                ],
            ],
            'exceptionMessage' => 'paymentType: This value should be of type string.',
            'version' => null,
        ];

        yield 'null constructor argument in input DTO with serializedName attribute' => [
            'url' => '/payments-dto-with-constructor',
            'json' => ['type' => null],
            'violations' => [
                'type' => [
                    'This value should be of type string.',
                ],
            ],
            'exceptionMessage' => 'paymentType: This value should be of type string.',
            'version' => null,
        ];

        yield 'Item not found by IRI v3' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'printingHouse' => '/printing-houses/2',
                'title' => 'some title',
                'weight' => 11,
            ],
            'violations' => [
                'printingHouse' => [
                    'Item not found for "/printing-houses/2".',
                ],
            ],
            'exceptionMessage' => 'printingHouse: This value should be of type ' .
                'EonX\\EasyApiPlatform\\Tests\\Fixture\\App\\EasyErrorHandler\\ApiResource\\PrintingHouse.',
            'version' => 3,
        ];

        yield 'Item not found by IRI' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'printingHouse' => '/printing-houses/2',
                'title' => 'some title',
                'weight' => 11,
            ],
            'violations' => [
                'printingHouse' => [
                    'Item not found for "/printing-houses/2".',
                ],
            ],
            'exceptionMessage' => 'printingHouse: This value should be of type PrintingHouse.',
            'version' => 4,
        ];
    }

    /**
     * @see testBuildErrorResponseWhenInvalidFormat
     * @see testBuildExtendedErrorResponseWhenInvalidFormat
     */
    public static function provideDataForBuildErrorResponseWhenInvalidFormat(): iterable
    {
        yield 'Body is null' => [
            'body' => null,
        ];

        yield 'Body is empty string' => [
            'body' => '',
        ];

        yield 'Body is not a valid JSON' => [
            'body' => 'some invalid JSON',
        ];

        yield 'Body is malformed JSON' => [
            'body' => '{"some": "invalid" "json": "format"}',
        ];
    }

    /**
     * @see testDoNotBuildErrorResponse
     * @see testDoNotBuildExtendedErrorResponse
     */
    public static function provideDataForDoNotBuildErrorResponse(): iterable
    {
        yield 'exception not supported by builders' => [
            'url' => '/dummies',
            'json' => [
                'dummyB' => 'some string',
            ],
            'exceptionClass' => DummyBException::class,
            'exceptionMessage' => 'This exception will NOT be handled by API Platform error builders',
        ];

        yield 'exception throw outside API Platform denormalizer' => [
            'url' => '/dummy-action',
            'json' => [],
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'Exception supported by API Platform Builders, but thrown ' .
                'outside API Platform denormalization logic.',
        ];

        yield 'default error when supported exception and unknown exception message' => [
            'url' => '/dummies',
            'json' => [
                'dummyA' => 'some string',
            ],
            'exceptionClass' => UnexpectedValueException::class,
            'exceptionMessage' => 'This exception will NOT be handled by API Platform error'
                . ' builders, because it message is not supported by them.',
        ];
    }

    #[DataProvider('provideDataForBuildErrorResponse')]
    public function testBuildErrorResponse(
        string $url,
        array $json,
        array $violations,
        string $exceptionMessage,
        ?int $version = null,
    ): void {
        if ($version !== null && \class_exists(InstalledVersions::class)) {
            $installedVersion = InstalledVersions::getVersion('api-platform/core');

            if (
                $installedVersion !== null
                && (
                    \version_compare($installedVersion, (string)$version, '>=')
                    && \version_compare($installedVersion, (string)($version + 1), '<')
                ) === false
            ) {
                self::markTestSkipped(
                    'This test requires API Platform version >=' . $version . ' and < ' . ($version + 1) . '.'
                );
            }
        }
        $response = self::$client->request('POST', $url, ['json' => $json]);

        $responseData = $response->toArray(false);
        self::assertSame(400, $response->getStatusCode());
        self::assertArrayStructure(
            [
                'custom_code',
                'custom_message',
                'custom_time',
                'custom_violations' => [],
            ],
            $responseData
        );
        self::assertCount(\count($responseData['custom_violations']), $violations, 'Please arrange violations.');
        self::assertArraySubset(
            [
                'custom_code' => 123,
                'custom_message' => 'Validation failed.',
                'custom_violations' => $violations,
            ],
            $responseData
        );
    }

    #[DataProvider('provideDataForBuildErrorResponseWhenInvalidFormat')]
    public function testBuildErrorResponseWhenInvalidFormat(mixed $body): void
    {
        $response = self::$client->request('POST', '/books', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
        ]);

        $responseData = $response->toArray(false);
        self::assertSame(400, $response->getStatusCode());
        self::assertArrayStructure(
            [
                'custom_code',
                'custom_message',
                'custom_time',
                'custom_violations' => [],
            ],
            $responseData
        );
        self::assertArraySubset(
            [
                'custom_code' => 123,
                'custom_message' => 'Validation failed.',
                'custom_violations' => [
                    'The input data is misformatted.',
                ],
            ],
            $responseData
        );
    }

    #[DataProvider('provideDataForBuildErrorResponse')]
    public function testBuildExtendedErrorResponse(
        string $url,
        array $json,
        array $violations,
        string $exceptionMessage,
        ?int $version = null,
    ): void {
        if ($version !== null && \class_exists(InstalledVersions::class)) {
            $installedVersion = InstalledVersions::getVersion('api-platform/core');

            if (
                $installedVersion !== null
                && (
                    \version_compare($installedVersion, (string)$version, '>=')
                    && \version_compare($installedVersion, (string)($version + 1), '<')
                ) === false
            ) {
                self::markTestSkipped(
                    'This test requires API Platform version >=' . $version . ' and < ' . ($version + 1) . '.'
                );
            }
        }
        $chainVerboseStrategy = self::getService(VerboseStrategyInterface::class);
        self::setPrivatePropertyValue($chainVerboseStrategy, 'verbose', true);

        $response = self::$client->request('POST', $url, ['json' => $json]);

        $responseData = $response->toArray(false);
        self::assertSame(400, $response->getStatusCode());
        self::assertArrayStructure(
            [
                'custom_code',
                'custom_exception' => [
                    'custom_class',
                    'custom_file',
                    'custom_line',
                    'custom_message',
                    'custom_trace' => [],
                ],
                'custom_message',
                'custom_time',
                'custom_violations' => [],
            ],
            $responseData
        );
        self::assertCount(\count($responseData['custom_violations']), $violations, 'Please arrange violations.');
        self::assertArraySubset(
            [
                'custom_code' => 123,
                'custom_exception' => [
                    'custom_message' => $exceptionMessage,
                ],
                'custom_message' => 'Validation failed.',
                'custom_violations' => $violations,
            ],
            $responseData
        );
    }

    #[DataProvider('provideDataForBuildErrorResponseWhenInvalidFormat')]
    public function testBuildExtendedErrorResponseWhenInvalidFormat(mixed $body): void
    {
        $chainVerboseStrategy = self::getService(VerboseStrategyInterface::class);
        self::setPrivatePropertyValue($chainVerboseStrategy, 'verbose', true);

        $response = self::$client->request('POST', '/books', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
        ]);

        $responseData = $response->toArray(false);
        self::assertSame(400, $response->getStatusCode());
        self::assertArrayStructure(
            [
                'custom_code',
                'custom_exception' => [
                    'custom_class',
                    'custom_file',
                    'custom_line',
                    'custom_message',
                    'custom_trace' => [],
                ],
                'custom_message',
                'custom_time',
                'custom_violations' => [],
            ],
            $responseData
        );
        self::assertArraySubset(
            [
                'custom_code' => 123,
                'custom_exception' => [
                    'custom_message' => 'Syntax error',
                ],
                'custom_message' => 'Validation failed.',
                'custom_violations' => [
                    'The input data is misformatted.',
                ],
            ],
            $responseData
        );
    }

    /**
     * @param class-string<\Throwable> $exceptionClass
     */
    #[DataProvider('provideDataForDoNotBuildErrorResponse')]
    public function testDoNotBuildErrorResponse(
        string $url,
        array $json,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $response = self::$client->request('POST', $url, ['json' => $json]);

        $responseData = $response->toArray(false);
        self::assertSame(500, $response->getStatusCode());
        self::assertArrayStructure(
            [
                'custom_code',
                'custom_message',
                'custom_time',
            ],
            $responseData
        );
        self::assertFalse(\array_key_exists('custom_violations', $responseData));
        self::assertArraySubset(
            [
                'custom_code' => 0,
                'custom_message' => 'Oops, something went wrong.',
            ],
            $responseData
        );
    }

    /**
     * @param class-string<\Throwable> $exceptionClass
     */
    #[DataProvider('provideDataForDoNotBuildErrorResponse')]
    public function testDoNotBuildExtendedErrorResponse(
        string $url,
        array $json,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $chainVerboseStrategy = self::getService(VerboseStrategyInterface::class);
        self::setPrivatePropertyValue($chainVerboseStrategy, 'verbose', true);

        $response = self::$client->request('POST', $url, ['json' => $json]);

        $responseData = $response->toArray(false);
        self::assertSame(500, $response->getStatusCode());
        self::assertArrayStructure(
            [
                'custom_code',
                'custom_exception' => [
                    'custom_class',
                    'custom_file',
                    'custom_line',
                    'custom_message',
                    'custom_trace' => [],
                ],
                'custom_message',
                'custom_time',
            ],
            $responseData
        );
        self::assertFalse(\array_key_exists('custom_violations', $responseData));
        self::assertArraySubset(
            [
                'custom_code' => 0,
                'custom_exception' => [
                    'custom_class' => $exceptionClass,
                    'custom_message' => $exceptionMessage,
                ],
                'custom_message' => 'Oops, something went wrong.',
            ],
            $responseData
        );
    }
}
