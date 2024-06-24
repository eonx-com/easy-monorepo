<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Generation\Common\Generator;

use EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException;
use EonX\EasyBankFiles\Tests\Stub\Generation\Common\Generator\GeneratorStub;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;

final class AbstractGeneratorTest extends AbstractUnitTestCase
{
    /**
     * Should throw exception if target is not an object.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
     */
    public function testShouldThrowExceptionIfTargetIsNotAnObject(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeneratorStub([], ['for-coverage']);
    }
}
