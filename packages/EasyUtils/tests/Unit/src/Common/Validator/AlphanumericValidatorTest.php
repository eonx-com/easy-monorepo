<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Validator;

use EonX\EasyUtils\Common\Constraint\Alphanumeric;
use EonX\EasyUtils\Common\Validator\AlphanumericValidator;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class AlphanumericValidatorTest extends AbstractUnitTestCase
{
    /**
     * @see testValidateFailsWithRegexFailedError
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'Invalid value #1' => ['1004^%45470'];
        yield 'Invalid value #2' => ['53+04085615'];
        yield 'Invalid value #3' => ['HGHD*#11111'];
        yield 'Invalid value #4' => ['YR#*JD1dsss'];
    }

    /**
     * @see testValidateSucceedsWithValidValue
     */
    public static function provideValidValues(): iterable
    {
        yield 'Valid value #1' => ['ADFSD3424DD'];
        yield 'Valid value #2' => ['FDSGFH32dGD'];
        yield 'Valid value #3' => ['9dsGdsd76ds'];
        yield 'Valid value #4' => ['10000000000'];
        yield 'Empty string' => [''];
        yield 'Null value' => [null];
    }

    #[DataProvider('provideInvalidValues')]
    public function testValidateFailsWithRegexFailedError(string $value): void
    {
        $validator = new AlphanumericValidator();
        $constraint = new Alphanumeric();
        $violationBuilder = $this->mockConstraintViolationBuilder(
            Alphanumeric::INVALID_ALPHANUMERIC_ERROR
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
                // Valid value
                return 'HG8760SDL8';
            }
        };
        $validator = new AlphanumericValidator();
        $constraint = new Alphanumeric();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($class, $constraint);

        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('provideValidValues')]
    public function testValidateSucceedsWithValidValue(mixed $value): void
    {
        $validator = new AlphanumericValidator();
        $constraint = new Alphanumeric();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new AlphanumericValidator();
        $constraint = new class() extends Constraint {
        };
        $value = 'some-value';
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyUtils\Common\Constraint\Alphanumeric"'
        );

        $validator->validate($value, $constraint);
    }

    public function testValidateThrowsUnexpectedValueExceptionIfObjectGiven(): void
    {
        $value = new stdClass();
        $validator = new AlphanumericValidator();
        $constraint = new Alphanumeric();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "stdClass" given');

        $validator->validate($value, $constraint);
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
