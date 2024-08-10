<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\File;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileHeader;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileTrailer;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\ResultsContext;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
final class FileTest extends AbstractUnitTestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'header' => new FileHeader(),
            'trailer' => new FileTrailer(),
        ];

        $file = new File(new ResultsContext([], [], [], [], []), $data);

        self::assertInstanceOf(FileHeader::class, $file->getHeader());
        self::assertIsArray($file->getGroups());
        self::assertInstanceOf(FileTrailer::class, $file->getTrailer());
    }
}
