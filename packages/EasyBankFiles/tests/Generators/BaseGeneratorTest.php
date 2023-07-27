<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Generators;

use EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException;
use EonX\EasyBankFiles\Tests\Generators\Stubs\GeneratorStub;

final class BaseGeneratorTest extends TestCase
{
    /**
     * Should throw exception if target is not an object.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException
     */
    public function testShouldThrowExceptionIfTargetIsNotAnObject(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeneratorStub([], ['for-coverage']);
    }
}
