<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Csv;

use EonX\EasyUtils\Csv\CsvParserConfig;
use EonX\EasyUtils\Csv\CsvParserConfigInterface;
use EonX\EasyUtils\Csv\CsvWithHeadersParser;
use EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException;
use EonX\EasyUtils\Csv\FromFileCsvContentsProvider;
use EonX\EasyUtils\Tests\AbstractTestCase;

final class CsvWithHeadersParserTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestFromFile(): iterable
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
    }

    /**
     * @return iterable<mixed>
     */
    public function providerTestFromFileForException(): iterable
    {
        yield 'Value for required header missing' => [
            __DIR__ . '/fixtures/missing_value_for_required_header.csv',
            CsvParserConfig::create(['required']),
            MissingValueForRequiredHeadersException::class,
        ];
    }

    /**
     * @param mixed[] $expected
     *
     * @dataProvider providerTestFromFile
     *
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingRequiredHeadersException
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException
     */
    public function testFromFile(string $filename, CsvParserConfigInterface $config, array $expected): void
    {
        $parser = new CsvWithHeadersParser();
        $result = $parser->parse(new FromFileCsvContentsProvider($filename), $config);
        $result = $result instanceof \Traversable ? \iterator_to_array($result) : $result;

        self::assertEquals($expected, $result);
    }

    /**
     * @phpstan-param class-string<\Throwable> $expectedException
     *
     * @dataProvider providerTestFromFileForException
     *
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingRequiredHeadersException
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException
     */
    public function testFromFileForException(
        string $filename,
        CsvParserConfigInterface $config,
        string $expectedException
    ): void {
        $this->expectException($expectedException);

        $parser = new CsvWithHeadersParser();
        $result = $parser->parse(new FromFileCsvContentsProvider($filename), $config);

        if ($result instanceof \Traversable) {
            \iterator_to_array($result);
        }
    }
}
