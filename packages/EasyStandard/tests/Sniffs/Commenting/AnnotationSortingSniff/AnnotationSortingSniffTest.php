<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Commenting\AnnotationSortingSniff;

use EonX\EasyStandard\Sniffs\Commenting\AnnotationSortingSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AnnotationSortingSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<SmartFileInfo|int>
     */
    public function providerTestSniff(): iterable
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/AnnotationSortingSniffTest.php.inc'), 1];
    }

    /**
     * @dataProvider providerTestSniff()
     */
    public function testSniff(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfoWithErrorCountOf($smartFileInfo, 1);
    }

    protected function getCheckerClass(): string
    {
        return AnnotationSortingSniff::class;
    }

    /**
     * @return mixed[]
     */
    protected function getCheckerConfiguration(): array
    {
        return [
            'alwaysTopAnnotations' => ['@param', '@return', '@throws'],
        ];
    }
}
