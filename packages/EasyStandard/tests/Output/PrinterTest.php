<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Output;

use EonX\EasyStandard\Output\Printer;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EonX\EasyStandard\Output\Printer
 */
final class PrinterTest extends TestCase
{
    /**
     * @return mixed[]
     *
     * @see testPrintNodesSucceeds
     */
    public function providePrintData(): array
    {
        return [
            'multi line array' => [
                'expectedOutput' => "[
    'test1' => 'test1',
    'test2' => 'test2',
]",
                'multiline' => true,
            ],
            'multi line array with indentLevel' => [
                'expectedOutput' => "[
        'test1' => 'test1',
        'test2' => 'test2',
    ]",
                'multiline' => true,
                'indentLevel' => 4,
            ],
            'single line array' => [
                'expectedOutput' => "['test1' => 'test1', 'test2' => 'test2']",
                'multiline' => false,
            ],
        ];
    }

    /**
     * @param string $expectedOutput
     * @param bool $multiline
     * @param int|null $indentLevel
     *
     * @dataProvider providePrintData
     */
    public function testPrintNodesSucceeds(string $expectedOutput, bool $multiline, ?int $indentLevel = null): void
    {
        $indentLevel = $indentLevel ?? 0;
        $arrayItem1 = new ArrayItem(new String_('test1'), new String_('test1'));
        $arrayItem2 = new ArrayItem(new String_('test2'), new String_('test2'));
        if ($multiline) {
            $arrayItem1->setAttribute('multiLine', 'no-matter');
            $arrayItem2->setAttribute('multiLine', 'no-matter');
        }
        $array = new Array_([$arrayItem1, $arrayItem2], [
            'kind' => Array_::KIND_SHORT,
        ]);
        $printer = new Printer();
        (function ($method) {
            return $this->{$method}();
        })->call($printer, 'resetState');
        $printer->setStartIndentLevel($indentLevel);

        $result = $printer->printNodes([$array]);

        self::assertSame($expectedOutput, $result);
    }
}
