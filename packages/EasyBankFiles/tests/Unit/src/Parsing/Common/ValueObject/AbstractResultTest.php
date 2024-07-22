<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Common\ValueObject;

use EonX\EasyBankFiles\Tests\Stub\ValueObject\ResultStub;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

final class AbstractResultTest extends AbstractUnitTestCase
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
