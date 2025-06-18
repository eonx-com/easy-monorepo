<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Ach\Parser;

use EonX\EasyBankFiles\Parsing\Ach\Parser\AchParser;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\Batch;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\BatchControl;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\BatchHeader;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\FileControl;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\FileHeader;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AchParser::class)]
final class AchParserTest extends AbstractUnitTestCase
{
    public function testParsesSuccessfully(): void
    {
        $parser = new AchParser($this->getSampleFileContents('ach_simpler.txt'));

        self::assertInstanceOf(FileHeader::class, $parser->getFileHeader());
        self::assertInstanceOf(FileControl::class, $parser->getFileControl());
        self::assertCount(1, $parser->getBatches());
        self::assertInstanceOf(Batch::class, $parser->getBatches()[0]);
        self::assertInstanceOf(BatchHeader::class, $parser->getBatches()[0]->getHeader());
        self::assertInstanceOf(BatchControl::class, $parser->getBatches()[0]->getControl());
        self::assertCount(7, $parser->getEntryDetailRecords());
    }

    public function testParsesWithAddendaSuccessfully(): void
    {
        $parser = new AchParser($this->getSampleFileContents('ach_with_addenda.txt'));

        self::assertInstanceOf(FileHeader::class, $parser->getFileHeader());
        self::assertInstanceOf(FileControl::class, $parser->getFileControl());
        self::assertCount(1, $parser->getBatches());
        self::assertCount(1, $parser->getEntryDetailRecords());
    }

    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(
            \realpath(__DIR__) . '/../../../../../Fixture/Parsing/Ach/' . $file
        ) ?: '';
    }
}
