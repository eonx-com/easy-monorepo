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
    public function testPrintNodesSucceeds(): void
    {
        $expectedOutput = "[
    'test1' => 'test1',
    'test2' => 'test2',
]";
        $arrayItem1 = new ArrayItem(new String_('test1'), new String_('test1'), false, ['multiLine' => true]);
        $arrayItem2 = new ArrayItem(new String_('test2'), new String_('test2'), false, ['multiLine' => true]);
        $array = new Array_([$arrayItem1, $arrayItem2], ['kind' => Array_::KIND_SHORT]);
        $printer = new Printer();
        (function ($method) {
            return $this->{$method}();
        })->call($printer, 'resetState');

        $result = $printer->printNodes([$array]);

        self::assertSame($expectedOutput, $result);
    }
}
