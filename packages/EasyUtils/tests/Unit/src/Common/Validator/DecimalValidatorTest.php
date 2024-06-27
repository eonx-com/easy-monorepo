<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Validator;

use EonX\EasyUtils\Common\Constraint\Decimal;
use EonX\EasyUtils\Common\Validator\DecimalValidator;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class DecimalValidatorTest extends AbstractUnitTestCase
{
    /**
     * @see testValidateFailsWithInvalidValue
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'Invalid value #1' => ['abc', 1, 3];
        yield 'Invalid value #2' => ['0.12345', 1, 3];
        yield 'Invalid value #3' => [0.12345, 1, 3];
        yield 'Invalid value #4' => [0.00001009, 1, 6];
        yield 'Invalid value #5' => [0.000001, 6, 8];
        yield 'Invalid value #6' => [0.0000011, 5, 6];
        yield 'Invalid value #7' => [1.0001, 5, 6];
        yield 'Invalid value #8' => [0.00001009, 1, 6];
        yield 'Invalid value #9' => [0.0000000002, 6, 8];
        yield 'Invalid value #10' => [-44.0001000002344, 6, 12];
        yield 'Invalid value #11' => ['420', 2, 3];
        yield 'Invalid value #12' => ['abc000', 1, 3];
        yield 'Invalid value #13' => ['Error string', 1, 3];
    }

    /**
     * @see testValidateSucceedsWithValidValue
     */
    public static function provideValidValues(): iterable
    {
        yield 'Valid value #1' => ['123', 1, 2];
        yield 'Valid value #2' => ['0', 1, 2];
        yield 'Valid value #3' => ['0.234', 1, 3];
        yield 'Valid value #4' => ['0.0', 1, 3];
        yield 'Valid value #5' => [0.01, 1, 3];
        yield 'Valid value #6' => [1, 1, 3];
        yield 'Valid value #7' => [0, 1, 3];
        yield 'Valid value #8' => [0.0000010, 1, 6];
        yield 'Valid value #9' => [7.0001000001, 1, 10];
        yield 'Valid value #10' => [0.000001, 6, 8];
        yield 'Valid value #11' => [-0.000001, 6, 8];
        yield 'Valid value #12' => [0.00001, 5, 8];
        yield 'Valid value #13' => [0.000002, 6, 8];
        yield 'Valid value #14' => [0.0000000002, 6, 10];
        yield 'Valid value #15' => [0.0000000002123, 6, 14];
        yield 'Valid value #16' => ['0.0000000002', 6, 10];
        yield 'Valid value #17' => [2432.0000200002123, 6, 17];
        yield 'Valid value #18' => ['3.000020000212300', 6, 13];
        yield 'Valid value #19' => [3.000020000212300, 6, 13];
        yield 'Empty string' => ['', 1, 3];
        yield 'Null value' => [null, 1, 3];
    }

    #[DataProvider('provideInvalidValues')]
    public function testValidateFailsWithInvalidValue(mixed $value, int $minPrecision, int $maxPrecision): void
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

    #[DataProvider('provideValidValues')]
    public function testValidateSucceedsWithValidValue(mixed $value, int $minPrecision, int $maxPrecision): void
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
            'Expected argument of type "EonX\EasyUtils\Common\Constraint\Decimal"'
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
        int $maxPrecision,
    ): ConstraintViolationBuilderInterface {
        /** @var \Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface $violationBuilder */
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
        ConstraintViolationBuilderInterface $violationBuilder,
    ): ExecutionContextInterface {
        /** @var \Symfony\Component\Validator\Context\ExecutionContextInterface $context */
        $context = $this->mock(ExecutionContextInterface::class, static function (MockInterface $mock) use (
            $message,
            $violationBuilder,
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
        /** @var \Symfony\Component\Validator\Context\ExecutionContextInterface $context */
        $context = $this->mock(ExecutionContextInterface::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('buildViolation')
                ->never();
        });

        return $context;
    }
}
