<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\Abn;
use EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\AbnValidator;
use EonX\EasyUtils\Tests\AbstractTestCase;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class AbnValidatorTest extends AbstractTestCase
{
    /**
     * @see testValidateFailsWithInvalidCharactersError
     */
    public static function provideAbnWithInvalidCharacters(): iterable
    {
        yield 'Contains letter' => ['1234567890a'];
        yield 'Contains special symbols' => ['1234(678900'];
    }

    /**
     * @see testValidateFailsWithModulusCalculationFailedError
     */
    public static function provideInvalidAbnValues(): iterable
    {
        yield 'Invalid Abn #1' => ['10043145470'];
        yield 'Invalid Abn #2' => ['53004085615'];
        yield 'Invalid Abn #3' => ['53004085615'];
        yield 'Invalid Abn #4' => ['10000000001'];
    }

    /**
     * @see testValidateThrowsUnexpectedValueException
     */
    public static function provideUnexpectedValues(): iterable
    {
        yield 'class without __toString' => [new stdClass(), 'Expected argument of type "string", "stdClass" given'];
        yield 'integer' => [53004085616, 'Expected argument of type "string", "int" given'];
    }

    /**
     * @see testValidateSucceedsWithValidAbn
     */
    public static function provideValidAbnValues(): iterable
    {
        yield 'Valid Abn #1' => ['53004085616'];
        yield 'Valid Abn #2' => ['28043145470'];
        yield 'Valid Abn #3' => ['91724684688'];
        yield 'Valid Abn #4' => ['10000000000'];
        yield 'Empty string' => [''];
        yield 'Null value' => [null];
    }

    /**
     * @dataProvider provideAbnWithInvalidCharacters
     */
    public function testValidateFailsWithInvalidCharactersError(string $abn): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $violationBuilder = $this->mockConstraintViolationBuilder(Abn::INVALID_CHARACTERS_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($abn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateFailsWithInvalidLengthError(): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $abn = '1234567890';
        $violationBuilder = $this->mockConstraintViolationBuilder(Abn::INVALID_LENGTH_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($abn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateFailsWithLeadingZeroError(): void
    {
        $abn = '03004085616';
        $validator = new AbnValidator();
        $constraint = new Abn();
        $violationBuilder = $this->mockConstraintViolationBuilder(Abn::LEADING_ZERO_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($abn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider provideInvalidAbnValues
     */
    public function testValidateFailsWithModulusCalculationFailedError(string $abn): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $violationBuilder = $this->mockConstraintViolationBuilder(Abn::MODULUS_CALCULATION_FAILED_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($abn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateSucceedsWithObjectToString(): void
    {
        $class = new class() {
            public function __toString()
            {
                // Valid Abn
                return '53004085616';
            }
        };
        $validator = new AbnValidator();
        $constraint = new Abn();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($class, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider provideValidAbnValues
     */
    public function testValidateSucceedsWithValidAbn(mixed $abn): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($abn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new AbnValidator();
        $constraint = new class() extends Constraint {
        };
        $abn = 'some-abn';
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\Abn"'
        );

        $validator->validate($abn, $constraint);
    }

    /**
     * @dataProvider provideUnexpectedValues
     */
    public function testValidateThrowsUnexpectedValueException(mixed $abn, string $message): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($message);

        $validator->validate($abn, $constraint);
    }

    private function mockConstraintViolationBuilder(string $code): ConstraintViolationBuilderInterface
    {
        /** @var \Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface $violationBuilder */
        $violationBuilder = $this->mock(
            ConstraintViolationBuilderInterface::class,
            static function (MockInterface $mock) use ($code): void {
                $mock->shouldReceive('setParameter')
                    ->once()
                    ->withNoArgs()
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
