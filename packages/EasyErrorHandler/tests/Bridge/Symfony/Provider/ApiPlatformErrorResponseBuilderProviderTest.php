<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Provider;

use EonX\EasyErrorHandler\Interfaces\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\AbstractApiTestCase;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\Exception\DummyBException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

final class ApiPlatformErrorResponseBuilderProviderTest extends AbstractApiTestCase
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
                'weight' => 11,
                'printingHouse' => '/printing-houses/1',
            ],
            'violations' => [
                'title' => [
                    'This value should not be blank.',
                    'This value should not be null.',
                ],
            ],
            'exceptionMessage' => 'Entity validation failed.',
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
            'exceptionMessage' => 'This value is not a valid date/time.',
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
            'exceptionMessage' => 'This value is not a valid date/time.',
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
            'exceptionMessage' => 'This value is not a valid date/time.',
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
                    'The type of the value should be "int", "string" given.',
                ],
            ],
            'exceptionMessage' => 'The type of the "pageCount" attribute must be "int", "string" given.',
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
                    'This value should not be null.',
                ],
            ],
            'exceptionMessage' => 'The type of the "pageCount" attribute must be "int", "NULL" given.',
        ];

        yield 'missing constructor argument' => [
            'url' => '/books',
            'json' => [],
            'violations' => [
                'description' => [
                    'This value should be present.',
                ],
            ],
            'exceptionMessage' => 'Cannot create an instance of ' .
                '"EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App\\ApiResource\\Book"' .
                ' from serialized data because its constructor requires parameter "description" to be present.',
        ];

        yield 'missing constructor argument with serializedName attribute' => [
            'url' => '/payments',
            'json' => [],
            'violations' => [
                'type' => [
                    'This value should be present.',
                ],
            ],
            'exceptionMessage' => 'Cannot create an instance of ' .
                '"EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App\\ApiResource\\Payment"' .
                ' from serialized data because its constructor requires parameter "paymentType" to be present.',
        ];

        yield 'null constructor argument with serializedName attribute' => [
            'url' => '/payments',
            'json' => ['type' => null],
            'violations' => [
                'type' => [
                    'This value should not be null.',
                ],
            ],
            'exceptionMessage' => 'The type of the "paymentType" attribute must be "string", "NULL" given.',
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
                'age' => [
                    'This value should be present.',
                ],
                'name' => [
                    'This value should be present.',
                ],
            ],
            'exceptionMessage' => 'Cannot create an instance of ' .
                '"EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App\\DataTransferObject\\Author"' .
                ' from serialized data because its constructor requires the following parameters to be present' .
                ' : "$name", "$age".',
        ];

        yield 'missing constructor argument in input DTO with serializedName attribute' => [
            'url' => '/payments-dto-with-constructor',
            'json' => [],
            'violations' => [
                'type' => [
                    'This value should be present.',
                ],
            ],
            'exceptionMessage' => 'Cannot create an instance of ' .
                '"EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App\\DataTransferObject' .
                '\\PaymentInputDtoWithConstructor"' .
                ' from serialized data because its constructor requires the following parameters to be present' .
                ' : "$paymentType".',
        ];

        yield 'null constructor argument in input DTO with serializedName attribute' => [
            'url' => '/payments-dto-with-constructor',
            'json' => ['type' => null],
            'violations' => [
                'type' => [
                    'The type of the value should be "string", "null" given.',
                ],
            ],
            'exceptionMessage' => 'The input data is misformatted.',
        ];

        yield 'invalid constructor argument type' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 'some string',
            ],
            'violations' => [
                'weight' => [
                    'The type of the value should be "int", "string" given.',
                ],
            ],
            'exceptionMessage' => 'The type of the "weight" attribute must be "int", "string" given.',
        ];

        yield 'input data is misformatted when invalid argument in DTO' => [
            'url' => '/categories-dto',
            'json' => [
                'name' => 'some name',
                'rank' => 'some string',
            ],
            'violations' => [
                'rank' => [
                    'The type of the value should be "int", "string" given.',
                ],
            ],
            'exceptionMessage' => 'The input data is misformatted.',
        ];

        yield 'missing constructor argument in DTO when input DTO' => [
            'url' => '/categories-dto-with-constructor',
            'json' => [
                'name' => 'some name',
            ],
            'violations' => [
                'rank' => [
                    'This value should be present.',
                ],
            ],
            'exceptionMessage' => 'Cannot create an instance of "EonX\\EasyErrorHandler\\Tests\\Bridge' .
                '\\Symfony\\Fixtures\\App\\DataTransferObject\\CategoryInputDtoWithConstructor" from serialized' .
                ' data because its constructor requires the following parameters to be present : "$rank".',
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
                'Invalid IRI "some invalid IRI".',
            ],
            'exceptionMessage' => 'Invalid IRI "some invalid IRI".',
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
                'Invalid IRI "some string".',
            ],
            'exceptionMessage' => 'Invalid IRI "some string".',
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
                    'This value should be PrintingHouse IRI.',
                ],
            ],
            'exceptionMessage' => 'EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App' .
                '\\ApiResource\\Book::__construct(): Argument #3 ($printingHouse) must be of type ' .
                'EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App\\ApiResource\\PrintingHouse, ' .
                'EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App\\ApiResource\\PublishingHouse given',
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

        // @todo Cases below should be supported by API Platform error builders
        yield 'date is empty string' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'publishedAt' => '',
                'printingHouse' => '/printing-houses/1',
            ],
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'The data is either not an string, an empty string, or null; you should pass a' .
                ' string that can be parsed with the passed format or a valid DateTime string.',
        ];

        yield 'date is NULL' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'publishedAt' => null,
                'printingHouse' => '/printing-houses/1',
            ],
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'The data is either not an string, an empty string, or null; you should pass a' .
                ' string that can be parsed with the passed format or a valid DateTime string.',
        ];

        yield 'invalid date format' => [
            'url' => '/books',
            'json' => [
                'description' => 'some description',
                'weight' => 11,
                'publishedAt' => 'some invalid date',
                'printingHouse' => '/printing-houses/1',
            ],
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'Failed to parse time string (some invalid date) at position 0 (s):' .
                ' The timezone could not be found in the database',
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
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'The type of the "age" attribute for class ' .
                '"EonX\\EasyErrorHandler\\Tests\\Bridge\\Symfony\\Fixtures\\App\\DataTransferObject\\Author"' .
                ' must be one of "int" ("string" given).',
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
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'The type of the "category" attribute must be "array" (nested document) or "string"' .
                ' (IRI), "integer" given.',
        ];

        yield 'null IRI when constructor parameter' => [
            'url' => '/books',
            'json' => [
                'title' => 'some title',
                'description' => 'some description',
                'weight' => 11,
                'printingHouse' => null,
            ],
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'The type of the "printingHouse" attribute must be "array" (nested document)' .
                ' or "string" (IRI), "NULL" given.',
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
            'exceptionClass' => NotNormalizableValueException::class,
            'exceptionMessage' => 'Nested documents for attribute "category" are not allowed. Use IRIs instead.',
        ];

        yield 'default error when supported exception and unknown exception message' => [
            'url' => '/dummies',
            'json' => [
                'dummyA' => 'some string',
            ],
            'exceptionClass' => UnexpectedValueException::class,
            'exceptionMessage' => 'This exception will be handled by API Platform error builders,' .
                ' but default error will be shown.',
        ];
    }

    #[DataProvider('provideDataForBuildErrorResponse')]
    public function testBuildErrorResponse(string $url, array $json, array $violations, string $exceptionMessage): void
    {
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
                'custom_code' => 0,
                'custom_message' => 'Validation failed.',
                'custom_violations' => $violations,
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
    ): void {
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
                'custom_code' => 0,
                'custom_exception' => [
                    'custom_message' => $exceptionMessage,
                ],
                'custom_message' => 'Validation failed.',
                'custom_violations' => $violations,
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
