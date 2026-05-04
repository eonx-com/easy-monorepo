<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Csv\Parser;

use EonX\EasyUtils\Csv\Exception\MissingValueForRequiredHeadersException;
use EonX\EasyUtils\Csv\Parser\CsvWithHeadersParser;
use EonX\EasyUtils\Csv\Provider\FromFileCsvContentsProvider;
use EonX\EasyUtils\Csv\ValueObject\CsvParserConfig;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Traversable;

final class CsvWithHeadersParserTest extends AbstractUnitTestCase
{
    /**
     * @see testFromFile
     */
    public static function provideFromFileData(): iterable
    {
        yield 'Simple file' => [
            __DIR__ . '/../../../../Fixture/Csv/simple_file.csv',
            CsvParserConfig::create(),
            [
                [
                    'aHeader' => 'aValue',
                    'header1' => 'value1',
                    'header2' => 'value2',
                    'header4' => 'value4',
                    'header3' => 'value3',
                    'integer' => 0,
                ],
            ],
        ];

        yield 'Simple file with transformer' => [
            __DIR__ . '/../../../../Fixture/Csv/simple_file.csv',
            CsvParserConfig::create(recordTransformers: [
                static fn (array $record): array => \array_change_key_case($record, \CASE_UPPER),
            ]),
            [
                [
                    'AHEADER' => 'aValue',
                    'HEADER1' => 'value1',
                    'HEADER2' => 'value2',
                    'HEADER4' => 'value4',
                    'HEADER3' => 'value3',
                    'INTEGER' => 0,
                ],
            ],
        ];

        yield 'Group prefixes' => [
            __DIR__ . '/../../../../Fixture/Csv/group_prefixes.csv',
            CsvParserConfig::create(null, ['accountMetadata', 'anotherPrefix']),
            [
                [
                    'accountMetadata' => [
                        'name' => 'name',
                        'age' => 'age',
                    ],
                    'anotherPrefix' => [
                        'key' => 'key',
                    ],
                    'not_prefixed' => 'not_prefixed',
                ],
                [
                    'accountMetadata' => [
                        'name' => 'name1',
                        'age' => 'age1',
                    ],
                    'anotherPrefix' => [
                        'key' => 'key1',
                    ],
                    'not_prefixed' => 'not_prefixed',
                ],
            ],
        ];

        yield 'Sanitize headers' => [
            __DIR__ . '/../../../../Fixture/Csv/hidden_characters.csv',
            CsvParserConfig::create(['account']),
            [
                [
                    'account' => 'L023704',
                    'eventSlug' => 'global-event-earn',
                    'notes' => 'August opening balance',
                    'value' => '3,598,212',
                ],
            ],
        ];

        yield 'Empty records not ignored' => [
            __DIR__ . '/../../../../Fixture/Csv/empty_records.csv',
            CsvParserConfig::create(),
            [
                [],
                [],
                [],
            ],
        ];

        yield 'Empty records ignored' => [
            __DIR__ . '/../../../../Fixture/Csv/empty_records.csv',
            CsvParserConfig::create(null, null, true),
            [],
        ];

        yield 'Empty records ignored with required headers' => [
            __DIR__ . '/../../../../Fixture/Csv/empty_records.csv',
            CsvParserConfig::create(['header1'], null, null, null, true),
            [],
        ];

        yield 'Ignore case for required headers' => [
            __DIR__ . '/../../../../Fixture/Csv/ignore_case.csv',
            CsvParserConfig::create(['requiredHeader'], ignoreHeadersCase: true),
            [
                [
                    'requiredHeader' => 'value1',
                    'secondheader' => 'value2',
                    'third_header' => 'value3',
                ],
            ],
        ];
    }

    /**
     * @see testFromFileForException
     */
    public static function provideFromFileForExceptionData(): iterable
    {
        yield 'Value for required header missing' => [
            __DIR__ . '/../../../../Fixture/Csv/missing_value_for_required_header.csv',
            CsvParserConfig::create(['required']),
            MissingValueForRequiredHeadersException::class,
        ];
    }

    /**
     * @throws \EonX\EasyUtils\Csv\Exception\MissingRequiredHeadersException
     * @throws \EonX\EasyUtils\Csv\Exception\MissingValueForRequiredHeadersException
     */
    #[DataProvider('provideFromFileData')]
    public function testFromFile(string $filename, CsvParserConfig $config, array $expected): void
    {
        $parser = new CsvWithHeadersParser();
        $result = $parser->parse(new FromFileCsvContentsProvider($filename), $config);
        $result = $result instanceof Traversable ? \iterator_to_array($result) : $result;

        self::assertEquals($expected, $result);
    }

    /**
     * @phpstan-param class-string<\Throwable> $expectedException
     *
     * @throws \EonX\EasyUtils\Csv\Exception\MissingRequiredHeadersException
     * @throws \EonX\EasyUtils\Csv\Exception\MissingValueForRequiredHeadersException
     */
    #[DataProvider('provideFromFileForExceptionData')]
    public function testFromFileForException(
        string $filename,
        CsvParserConfig $config,
        string $expectedException,
    ): void {
        $this->expectException($expectedException);

        $parser = new CsvWithHeadersParser();
        $result = $parser->parse(new FromFileCsvContentsProvider($filename), $config);

        if ($result instanceof Traversable) {
            \iterator_to_array($result);
        }
    }
}
