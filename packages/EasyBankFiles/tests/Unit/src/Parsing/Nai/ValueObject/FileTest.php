<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\File;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileHeader;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileTrailer;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\ResultsContextInterface;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use Mockery\MockInterface;
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

        /** @var \EonX\EasyBankFiles\Parsing\Nai\ValueObject\ResultsContextInterface $context */
        $context = $this->getMockWithExpectations(
            ResultsContextInterface::class,
            static function (MockInterface $context): void {
                $context
                    ->shouldReceive('getGroups')
                    ->once()
                    ->withNoArgs()
                    ->andReturn([]);
            }
        );

        $file = new File($context, $data);

        self::assertInstanceOf(FileHeader::class, $file->getHeader());
        self::assertIsArray($file->getGroups());
        self::assertInstanceOf(FileTrailer::class, $file->getTrailer());
    }
}
