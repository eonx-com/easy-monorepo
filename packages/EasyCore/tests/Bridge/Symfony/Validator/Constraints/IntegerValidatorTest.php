<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Integer;
use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\IntegerValidator;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\Validator\Constraints\IntegerValidator
 */
final class IntegerValidatorTest extends AbstractSymfonyTestCase
{
    /**
     * @return mixed[]
     *
     * @see testValidateSucceedsAndDoesNothing
     */
    public function provideEmptyValues(): array
    {
        return [
            'Empty value #1' => [''],
            'Empty value #2' => [null],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see provideInvalidValues
     */
    public function provideInvalidValues(): array
    {
        return [
            'Invalid value #1' => [1.25],
            'Invalid value #2' => ['1.25'],
            'Invalid value #3' => ['A#4'],
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
            'Valid value #1' => [123],
            'Valid value #2' => ['123'],
            'Valid value #3' => [-123],
            'Valid value #4' => ['-123'],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideEmptyValues
     */
    public function testValidateSucceedsAndDoesNothing($value): void
    {
        $validator = new IntegerValidator();
        $constraint = new Integer();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testValidateSucceedsWithInvalidValue($value): void
    {
        $validator = new IntegerValidator();
        $constraint = new Integer();
        $violationBuilder = $this->mockConstraintViolationBuilder(Integer::INVALID_INTEGER_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testValidateSucceedsWithValidValue($value): void
    {
        $validator = new IntegerValidator();
        $constraint = new Integer();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new IntegerValidator();
        $constraint = new class() extends Constraint {
        };
        $value = 12345;
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Integer"',
        );

        $validator->validate($value, $constraint);
    }

    private function mockConstraintViolationBuilder(string $code): ConstraintViolationBuilderInterface
    {
        /** @var \Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface $violationBuilder */
        $violationBuilder = $this->mock(
            ConstraintViolationBuilderInterface::class,
            static function (MockInterface $mock) use ($code): void {
                $mock->shouldReceive('setCode')
                    ->once()
                    ->with($code)
                    ->andReturnSelf();

                $mock->shouldReceive('addViolation')
                    ->once()
                    ->withNoArgs()
                    ->andReturnSelf();
            },
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
