<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Decimal;
use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\DecimalValidator;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\Validator\Constraints\DecimalValidator
 */
final class DecimalValidatorTest extends AbstractSymfonyTestCase
{
    /**
     * @return mixed[]
     *
     * @see testValidateFailsWithInvalidValue
     */
    public function provideInvalidValues(): array
    {
        return [
            'Invalid value #1' => ['abc', 1, 3],
            'Invalid value #2' => ['0.12345', 1, 3],
            'Invalid value #3' => [0.12345, 1, 3],
            'Invalid value #4' => [0.00001009, 1, 6],
            'Invalid value #5' => [0.000001, 6, 8],
            'Invalid value #6' => [0.0000011, 5, 6],
            'Invalid value #7' => [1.0001, 5, 6],
            'Invalid value #8' => [0.00001009, 1, 6],
            'Invalid value #9' => [0.0000000002, 6, 8],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testValidateSucceedsWithValidValue
     */
    public function provideValidValues(): array
    {
        return [
            'Valid value #1' => ['123', 1, 2],
            'Valid value #2' => ['0', 1, 2],
            'Valid value #3' => ['0.234', 1, 3],
            'Valid value #4' => ['0.0', 1, 3],
            'Valid value #5' => [0.01, 1, 3],
            'Valid value #6' => [1, 1, 3],
            'Valid value #7' => [0, 1, 3],
            'Valid value #8' => [0.0000010, 1, 6],
            'Valid value #9' => [7.0001000001, 1, 10],
            'Valid value #10' => [0.000001, 6, 8],
            'Valid value #11' => [-0.000001, 6, 8],
            'Valid value #12' => [0.00001, 5, 8],
            'Valid value #13' => [0.000002, 6, 8],
            'Valid value #14' => [0.0000000002, 6, 10],
            'Valid value #15' => ['0.0000000002', 6, 10],
            'Empty string' => ['', 1, 3],
            'Null value' => [null, 1, 3],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testValidateFailsWithInvalidValue($value, int $minPrecision, int $maxPrecision): void
    {
        $validator = new DecimalValidator();
        $constraint = new Decimal(\compact('minPrecision', 'maxPrecision'));
        $violationBuilder = $this->mockConstraintViolationBuilder(
            Decimal::INVALID_DECIMAL_ERROR,
            $minPrecision,
            $maxPrecision
        );
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateSucceedsWithObjectToString(): void
    {
        $class = new class() {
            public function __toString()
            {
                return '0.123';
            }
        };
        $validator = new DecimalValidator();
        $constraint = new Decimal([
            'minPrecision' => 1,
            'maxPrecision' => 3,
        ]);
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($class, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testValidateSucceedsWithValidValue($value, int $minPrecision, int $maxPrecision): void
    {
        $validator = new DecimalValidator();
        $constraint = new Decimal(\compact('minPrecision', 'maxPrecision'));
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateThrowsConstraintDefinitionExceptionExceptionWhenMaxPrecisionLessThanMinPrecision(): void
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The "maxPrecision" option must be an integer greater than "minPrecision".');

        new Decimal([
            'minPrecision' => 2,
            'maxPrecision' => 1,
        ]);
    }

    public function testValidateThrowsConstraintDefinitionExceptionExceptionWhenMinPrecisionLessThanOne(): void
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The "minPrecision" option must be an integer greater than zero.');

        new Decimal([
            'minPrecision' => 0,
            'maxPrecision' => 2,
        ]);
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new DecimalValidator();
        $constraint = new class() extends Constraint {
        };
        $value = 'some-value';
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Decimal"'
        );

        $validator->validate($value, $constraint);
    }

    public function testValidateThrowsUnexpectedValueExceptionIfObjectGiven(): void
    {
        $value = new stdClass();
        $validator = new DecimalValidator();
        $constraint = new Decimal([
            'minPrecision' => 1,
            'maxPrecision' => 2,
        ]);
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "scalar", "stdClass" given');

        $validator->validate($value, $constraint);
    }

    private function mockConstraintViolationBuilder(
        string $code,
        int $minPrecision,
        int $maxPrecision
    ): ConstraintViolationBuilderInterface {
        /** @var ConstraintViolationBuilderInterface $violationBuilder */
        $violationBuilder = $this->mock(
            ConstraintViolationBuilderInterface::class,
            static function (MockInterface $mock) use ($code, $minPrecision, $maxPrecision): void {
                $mock->shouldReceive('setParameter')
                    ->once()
                    ->withArgs(['{{ minPrecision }}', (string)$minPrecision])
                    ->andReturnSelf();

                $mock->shouldReceive('setParameter')
                    ->once()
                    ->withArgs(['{{ maxPrecision }}', (string)$maxPrecision])
                    ->andReturnSelf();

                $mock->shouldReceive('setCode')
                    ->once()
                    ->with($code)
                    ->andReturnSelf();

                $mock->shouldReceive('addViolation')
                    ->once()
                    ->withNoArgs()
                    ->andReturnSelf();
            }
        );

        return $violationBuilder;
    }

    private function mockExecutionContextWithBuildViolation(
        string $message,
        ConstraintViolationBuilderInterface $violationBuilder
    ): ExecutionContextInterface {
        /** @var ExecutionContextInterface $context */
        $context = $this->mock(ExecutionContextInterface::class, static function (MockInterface $mock) use (
            $message,
            $violationBuilder
        ): void {
            $mock->shouldReceive('buildViolation')
                ->once()
                ->with($message)
                ->andReturn($violationBuilder);
        });

        return $context;
    }

    private function mockExecutionContextWithoutCalls(): ExecutionContextInterface
    {
        /** @var ExecutionContextInterface $context */
        $context = $this->mock(ExecutionContextInterface::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('buildViolation')
                ->never();
        });

        return $context;
    }
}
