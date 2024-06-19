<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Common\Parser;

use EonX\EasyBankFiles\Tests\Stub\Parsing\Common\Parser\ParserStub;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;

final class AbstractParserTest extends AbstractUnitTestCase
{
    /**
     * Should set the content.
     */
    public function testShouldSetContent(): void
    {
        $content = 'sample content';
        $parser = new ParserStub($content);

        $result = self::getPrivatePropertyValue($parser, 'contents');

        self::assertSame($content, $result);
    }
}
