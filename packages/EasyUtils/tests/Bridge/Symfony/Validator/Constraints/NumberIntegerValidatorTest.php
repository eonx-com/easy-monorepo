<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\NumberInteger;
use EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\NumberIntegerValidator;
use EonX\EasyUtils\ValueObjects\Number;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @covers \EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\NumberIntegerValidator
 */
final class NumberIntegerValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @dataProvider provideEmptyValues
     */
    public function testItSucceedsWithEmptyValues(mixed $value): void
    {
        $this->validator->validate($value, new NumberInteger());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testItSucceedsWithInvalidValues(mixed $value): void
    {
        $this->validator->validate($value, new NumberInteger());

        $this
            ->buildViolation('This value should be of type integer.')
            ->assertRaised();
    }

    /**
     * @dataProvider provideValidValues
     */
    public function testItSucceedsWithValidValues(mixed $value): void
    {
        $this->validator->validate($value, new NumberInteger());

        $this->assertNoViolation();
    }

    public function testItThrowsThrowsUnexpectedTypeException(): void
    {
        $constraint = new class() extends Constraint {
        };
        $value = 12345;
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\NumberInteger"'
        );

        $this->validator->validate($value, $constraint);
    }

    protected function createValidator(): NumberIntegerValidator
    {
        return new NumberIntegerValidator();
    }

    /**
     * @return mixed[]
     *
     * @see testValidateSucceedsAndDoesNothing
     */
    protected function provideEmptyValues(): array
    {
        return [
            'Empty value #1' => [''],
            'Empty value #2' => [null],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testItSucceedsWithInvalidValues
     */
    protected function provideInvalidValues(): array
    {
        return [
            'Invalid value #1' => [new Number('1.25', 2)],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testItSucceedsWithValidValues
     */
    protected function provideValidValues(): array
    {
        return [
            'Valid value #1' => [new Number(123)],
            'Valid value #2' => [new Number(123, 2)],
            'Valid value #3' => [new Number('123')],
            'Valid value #4' => [new Number('123', 2)],
            'Valid value #5' => [new Number(-123)],
            'Valid value #6' => [new Number(-123, 2)],
            'Valid value #7' => [new Number('-123')],
            'Valid value #8' => [new Number('-123', 2)],
            'Valid value #9' => [new Number('62136871268376128763871268736128763871263876123761872368712')],
            'Valid value #10' => [new Number('62136871268376128763871268736128763871263876123761872368712', 2)],
            'Valid value #11' => [new Number('00001268376128763871268736128763871263876123761872368712')],
            'Valid value #12' => [new Number('00001268376128763871268736128763871263876123761872368712', 2)],
            'Valid value #13' => [new Number('-62136871268376128763871268736128763871263876123761872368712')],
            'Valid value #14' => [new Number('-62136871268376128763871268736128763871263876123761872368712', 2)],
            'Valid value #15' => [new Number('-00001268376128763871268736128763871263876123761872368712')],
            'Valid value #16' => [new Number('-00001268376128763871268736128763871263876123761872368712', 2)],
        ];
    }
}
