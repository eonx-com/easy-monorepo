<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Abn;
use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\AbnValidator;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\Validator\Constraints\AbnValidator
 */
final class AbnValidatorTest extends AbstractSymfonyTestCase
{
    /**
     * @return string[][]
     *
     * @see testValidateFailsWithInvalidCharactersError
     */
    public function provideAbnWithInvalidCharacters(): array
    {
        return [
            'Contains letter' => ['1234567890a'],
            'Contains special symbols' => ['1234(678900'],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testValidateThrowsUnexpectedValueException
     */
    public function provideUnexpectedValues(): array
    {
        return [
            'class without __toString' => [new stdClass(), 'Expected argument of type "string", "stdClass" given'],
            'integer' => [53004085616, 'Expected argument of type "string", "int" given'],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testValidateSucceedsWithValidAbn
     */
    public function provideValidAbnValues(): array
    {
        return [
            'Valid Abn #1' => ['53004085616'],
            'Valid Abn #2' => ['28043145470'],
            'Valid Abn #3' => ['91724684688'],
            'Valid Abn #4' => ['10000000000'],
            'Empty string' => [''],
            'Null value' => [null],
        ];
    }

    /**
     * @return string[][]
     *
     * @see testValidateFailsWithModulusCalculationFailedError
     */
    public function provideInvalidAbnValues(): array
    {
        return [
            'Invalid Abn #1' => ['10043145470'],
            'Invalid Abn #2' => ['53004085615'],
            'Invalid Abn #3' => ['53004085615'],
            'Invalid Abn #4' => ['10000000001'],
        ];
    }

    /**
     * @param mixed $abn
     *
     * @dataProvider provideValidAbnValues
     */
    public function testValidateSucceedsWithValidAbn($abn): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $context = $this->mockExecutionContextWithoutCalls();
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

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new AbnValidator();
        $constraint = new class() extends Constraint {
        };
        $abn = 'some-abn';
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Abn"'
        );

        $validator->validate($abn, $constraint);
    }

    /**
     * @param mixed $abn
     *
     * @dataProvider provideUnexpectedValues
     */
    public function testValidateThrowsUnexpectedValueException($abn, string $message): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($message);

        $validator->validate($abn, $constraint);
    }

    public function testValidateThrowsUnexpectedValueExceptionIfObjectGiven(): void
    {
        $abn = new stdClass();
        $validator = new AbnValidator();
        $constraint = new Abn();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "stdClass" given');

        $validator->validate($abn, $constraint);
    }

    public function testValidateThrowsUnexpectedValueExceptionIfNumberGiven(): void
    {
        $abn = 53004085616;
        $validator = new AbnValidator();
        $constraint = new Abn();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "int" given');

        $validator->validate($abn, $constraint);
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
