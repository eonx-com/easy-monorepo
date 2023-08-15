<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\Nai\Results\File;
use EonX\EasyBankFiles\Parsers\Nai\Results\Files\Header;
use EonX\EasyBankFiles\Parsers\Nai\Results\Files\Trailer;
use EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContextInterface;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
final class FileTest extends TestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'header' => new Header(),
            'trailer' => new Trailer(),
        ];

        /** @var \EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContextInterface $context */
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

        self::assertInstanceOf(Header::class, $file->getHeader());
        self::assertIsArray($file->getGroups());
        self::assertInstanceOf(Trailer::class, $file->getTrailer());
    }
}
