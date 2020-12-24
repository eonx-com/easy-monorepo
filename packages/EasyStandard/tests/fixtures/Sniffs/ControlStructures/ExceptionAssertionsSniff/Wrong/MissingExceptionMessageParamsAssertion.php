<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\fixtures\Sniffs\ControlStructures\ExceptionAssertionsSniff\Wrong;

use EonX\EasyStandard\Tests\fixtures\Sniffs\ControlStructures\ExceptionAssertionsSniff\TranslatableException;

final class MissingExceptionMessageParamsAssertion
{
    public function testValidateThrowsExceptionWithUnsupportedConstraint(): void
    {
        $this->safeCall(static function (){});

        $this->assertThrownException(TranslatableException::class);
        $this->assertThrownExceptionMessage('exceptions.business.unexpected_business_feature_type');
        $this->assertThrownExceptionUserMessage('exceptions.default_user_message');
        $this->assertThrownExceptionUserMessageParams([]);
    }
}
