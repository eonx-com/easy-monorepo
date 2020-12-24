<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\fixtures\Sniffs\ControlStructures\ExceptionAssertionsSniff\Correct;

use EonX\EasyStandard\Tests\fixtures\Sniffs\ControlStructures\ExceptionAssertionsSniff\NonTranslatableException;

class NonTranslatableExceptionTest
{
    public function testValidateThrowsExceptionWithUnsupportedConstraint(): void
    {
        $this->safeCall(static function (){});

        $this->assertThrownException(NonTranslatableException::class);
    }
}