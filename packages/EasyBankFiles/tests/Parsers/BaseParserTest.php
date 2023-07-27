<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers;

use EonX\EasyBankFiles\Tests\Parsers\Stubs\ParserStub;

final class BaseParserTest extends TestCase
{
    /**
     * Should set the content.
     *
     * @throws \ReflectionException
     */
    public function testShouldSetContent(): void
    {
        $content = 'sample content';

        $parser = new ParserStub($content);

        $property = $this->getProtectedProperty($parser::class, 'contents');

        self::assertSame($content, $property->getValue($parser));
    }
}
