<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Validator;

use EonX\EasyUtils\Common\Constraint\Abn;
use EonX\EasyUtils\Common\Constraint\Acn;
use EonX\EasyUtils\Common\Validator\AcnValidator;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class AcnValidatorTest extends AbstractUnitTestCase
{
    /**
     * @see testValidateFailsWithInvalidCharactersError
     */
    public static function provideAcnWithInvalidCharacters(): iterable
    {
        yield 'Contains letter' => ['12345678a'];
        yield 'Contains special symbols' => ['1234(6789'];
    }

    /**
     * @see testValidateSucceedsAndAddsComplementCalculationFailedError
     */
    public static function provideInvalidAcnValues(): iterable
    {
        yield 'Invalid Acn #1' => ['100431455'];
        yield 'Invalid Acn #2' => ['530040856'];
        yield 'Invalid Acn #3' => ['530040856'];
        yield 'Invalid Acn #4' => ['100000000'];
        yield 'Invalid Acn #5' => ['000000000'];
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
     * @see testValidateSucceeds
     */
    public static function provideValidAcnValues(): iterable
    {
        yield 'Valid Acn #1' => ['010499966'];
        yield 'Valid Acn #2' => ['007999975'];
        yield 'Valid Acn #3' => ['002749993'];
        yield 'Valid Acn #4' => ['006999980'];
        yield 'Valid Acn #5' => ['658996911'];
        yield 'Valid Acn #6' => ['601839561'];
        yield 'Valid Acn #7' => ['615958873'];
        yield 'Empty string' => [''];
        yield 'Null value' => [null];
    }

    #[DataProvider('provideAcnWithInvalidCharacters')]
    public function testValidateFailsWithInvalidCharactersError(string $acn): void
    {
        $validator = new AcnValidator();
        $constraint = new Acn();
        $violationBuilder = $this->mockConstraintViolationBuilder(Abn::INVALID_CHARACTERS_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($acn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateFailsWithInvalidLengthError(): void
    {
        $validator = new AcnValidator();
        $constraint = new Acn();
        $acn = '00349992';
        $violationBuilder = $this->mockConstraintViolationBuilder(Acn::INVALID_LENGTH_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($acn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('provideValidAcnValues')]
    public function testValidateSucceeds(?string $acn = null): void
    {
        $validator = new AcnValidator();
        $constraint = new Acn();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($acn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('provideInvalidAcnValues')]
    public function testValidateSucceedsAndAddsComplementCalculationFailedError(string $acn): void
    {
        $validator = new AcnValidator();
        $constraint = new Acn();
        $violationBuilder = $this->mockConstraintViolationBuilder(Acn::COMPLEMENT_CALCULATION_FAILED_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($acn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateSucceedsWithObjectToString(): void
    {
        $acn = new class() {
            public function __toString()
            {
                // Valid Acn
                return '007249989';
            }
        };
        $validator = new AcnValidator();
        $constraint = new Acn();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($acn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new AcnValidator();
        $constraint = new class() extends Constraint {
        };
        $acn = 'some-acn';
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyUtils\Common\Constraint\Acn"'
        );

        $validator->validate($acn, $constraint);
    }

    #[DataProvider('provideUnexpectedValues')]
    public function testValidateThrowsUnexpectedValueException(mixed $acn, string $message): void
    {
        $validator = new AcnValidator();
        $constraint = new Acn();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($message);

        $validator->validate($acn, $constraint);
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
