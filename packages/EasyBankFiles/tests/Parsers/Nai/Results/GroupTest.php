<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\Nai\Results\Group;
use EonX\EasyBankFiles\Parsers\Nai\Results\Groups\Header;
use EonX\EasyBankFiles\Parsers\Nai\Results\Groups\Trailer;
use EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContextInterface;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Group::class)]
final class GroupTest extends TestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'header' => new Header(),
            'index' => 2,
            'trailer' => new Trailer(),
        ];

        $setExpectations = static function (MockInterface $context) use ($data): void {
            $context
                ->shouldReceive('getFile')
                ->once()
                ->withNoArgs()
                ->andReturn(null);
            $context
                ->shouldReceive('getAccountsForGroup')
                ->once()
                ->withArgs([$data['index']])
                ->andReturn([]);
        };

        $context = $this->getMockWithExpectations(ResultsContextInterface::class, $setExpectations);

        $group = new Group($context, $data);

        self::assertIsArray($group->getAccounts());
        self::assertNull($group->getFile());
        self::assertInstanceOf(Header::class, $group->getHeader());
        self::assertInstanceOf(Trailer::class, $group->getTrailer());
    }
}
