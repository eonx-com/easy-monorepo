<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers;

use EonX\EasyBankFiles\Tests\Parsers\Stubs\ResultStub;
use PHPUnit\Framework\Attributes\Group;

final class BaseResultTest extends TestCase
{
    /**
     * Should return company name as biller.
     */
    #[Group('Base-Result')]
    public function testShouldReturnBiller(): void
    {
        $data = [
            'biller' => 'Company Name',
        ];

        $result = new ResultStub($data);

        self::assertSame($data['biller'], $result->getBiller());
    }

    /**
     * Should return null if attribute does not exist.
     */
    #[Group('Base-Result')]
    public function testShouldReturnNull(): void
    {
        $data = [
            'biller' => 'Company Name',
        ];

        $result = new ResultStub($data);

        self::assertNull($result->getWhatAttribute());
    }
}
