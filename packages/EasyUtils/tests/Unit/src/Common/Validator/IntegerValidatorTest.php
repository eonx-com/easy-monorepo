<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Validator;

use EonX\EasyUtils\Common\Constraint\Integer;
use EonX\EasyUtils\Common\Validator\IntegerValidator;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[CoversClass(IntegerValidator::class)]
final class IntegerValidatorTest extends AbstractUnitTestCase
{
    /**
     * @see testValidateSucceedsAndDoesNothing
     */
    public static function provideEmptyValues(): iterable
    {
        yield 'Empty value #1' => [''];
        yield 'Empty value #2' => [null];
    }

    /**
     * @see testValidateSucceedsWithInvalidValue
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'Invalid value #1' => [1.25];
        yield 'Invalid value #2' => ['1.25'];
        yield 'Invalid value #3' => ['A#4'];
    }

    /**
     * @see testValidateSucceedsWithValidValue
     */
    public static function provideValidValues(): iterable
    {
        yield 'Valid value #1' => [123];
        yield 'Valid value #2' => ['123'];
        yield 'Valid value #3' => [-123];
        yield 'Valid value #4' => ['-123'];
    }

    #[DataProvider('provideEmptyValues')]
    public function testValidateSucceedsAndDoesNothing(mixed $value): void
    {
        $validator = new IntegerValidator();
        $constraint = new Integer();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('provideInvalidValues')]
    public function testValidateSucceedsWithInvalidValue(mixed $value): void
    {
        $validator = new IntegerValidator();
        $constraint = new Integer();
        $violationBuilder = $this->mockConstraintViolationBuilder('integer.not_valid');
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($value, $constraint);

        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('provideValidValues')]
    public function testValidateSucceedsWithValidValue(mixed $value): void
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
            'Expected argument of type "EonX\EasyUtils\Common\Constraint\Integer", ' .
            '"Symfony\Component\Validator\Constraint@anonymous" given'
        );

        $validator->validate($value, $constraint);
    }

    private function mockConstraintViolationBuilder(string $code): ConstraintViolationBuilderInterface
    {
        return $this->mock(
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
            }
        );
    }

    private function mockExecutionContextWithBuildViolation(
        string $message,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): ExecutionContextInterface {
        return $this->mock(ExecutionContextInterface::class, static function (MockInterface $mock) use (
            $message,
            $violationBuilder,
        ): void {
            $mock->shouldReceive('buildViolation')
                ->once()
                ->with($message)
                ->andReturn($violationBuilder);
        });
    }

    private function mockExecutionContextWithoutCalls(): ExecutionContextInterface
    {
        return $this->mock(ExecutionContextInterface::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('buildViolation')
                ->never();
        });
    }
}
