<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Csv;

use EonX\EasyUtils\Csv\CsvParserConfig;
use EonX\EasyUtils\Csv\CsvParserConfigInterface;
use EonX\EasyUtils\Csv\CsvWithHeadersParser;
use EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException;
use EonX\EasyUtils\Csv\FromFileCsvContentsProvider;
use EonX\EasyUtils\Tests\AbstractTestCase;
use Traversable;

final class CsvWithHeadersParserTest extends AbstractTestCase
{
    /**
     * @see testFromFile
     */
    public static function providerTestFromFile(): iterable
    {
        yield 'Simple file' => [
            __DIR__ . '/fixtures/simple_file.csv',
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
            __DIR__ . '/fixtures/simple_file.csv',
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
            __DIR__ . '/fixtures/group_prefixes.csv',
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
            __DIR__ . '/fixtures/hidden_characters.csv',
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
            __DIR__ . '/fixtures/empty_records.csv',
            CsvParserConfig::create(),
            [
                [],
                [],
            ],
        ];

        yield 'Empty records ignored' => [
            __DIR__ . '/fixtures/empty_records.csv',
            CsvParserConfig::create(null, null, true),
            [],
        ];
    }

    /**
     * @see testFromFileForException
     */
    public static function providerTestFromFileForException(): iterable
    {
        yield 'Value for required header missing' => [
            __DIR__ . '/fixtures/missing_value_for_required_header.csv',
            CsvParserConfig::create(['required']),
            MissingValueForRequiredHeadersException::class,
        ];
    }

    /**
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingRequiredHeadersException
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException
     *
     * @dataProvider providerTestFromFile
     */
    public function testFromFile(string $filename, CsvParserConfigInterface $config, array $expected): void
    {
        $parser = new CsvWithHeadersParser();
        $result = $parser->parse(new FromFileCsvContentsProvider($filename), $config);
        $result = $result instanceof Traversable ? \iterator_to_array($result) : $result;

        self::assertEquals($expected, $result);
    }

    /**
     * @phpstan-param class-string<\Throwable> $expectedException
     *
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingRequiredHeadersException
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException
     *
     * @dataProvider providerTestFromFileForException
     */
    public function testFromFileForException(
        string $filename,
        CsvParserConfigInterface $config,
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
