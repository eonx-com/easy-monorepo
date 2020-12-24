<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\fixtures\Sniffs\ControlStructures\ExceptionAssertionsSniff\Wrong;

use EonX\EasyStandard\Tests\fixtures\Sniffs\ControlStructures\ExceptionAssertionsSniff\TranslatableException;

final class MissingUserMessageParamsAssertion
{
    public function testValidateThrowsExceptionWithUnsupportedConstraint(): void
    {
        $this->safeCall(static function (): void {
        });

        $this->assertThrownException(TranslatableException::class);
        $this->assertThrownExceptionMessage('exceptions.business.unexpected_business_feature_type');
        $this->assertThrownExceptionMessageParams(['type' => 'some-type']);
        $this->assertThrownExceptionUserMessage('exceptions.default_user_message');
    }
}
