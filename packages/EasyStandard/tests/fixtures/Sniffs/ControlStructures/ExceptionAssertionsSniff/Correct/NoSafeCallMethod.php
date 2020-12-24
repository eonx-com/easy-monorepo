<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\fixtures\Sniffs\ControlStructures\ExceptionAssertionsSniff\Correct;

final class NoSafeCallMethod
{
    public function testValidateThrowsExceptionWithUnsupportedConstraint(): void
    {
        $this->methodCall(static function (): void {
        });
    }
}
