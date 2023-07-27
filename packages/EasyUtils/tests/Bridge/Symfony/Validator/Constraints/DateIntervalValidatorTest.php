<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\DateInterval;
use EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\DateIntervalValidator;
use EonX\EasyUtils\Tests\AbstractTestCase;
use Mockery\MockInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class DateIntervalValidatorTest extends AbstractTestCase
{
    /**
     * @see testValidateFailsWithInvalidDateIntervalValues
     */
    public static function provideInvalidDateIntervalValues(): iterable
    {
        yield 'Invalid date interval #1' => ['P4D1Y'];
        yield 'Invalid date interval #2' => ['6MT'];
        yield 'Invalid date interval #3' => ['abc'];
        yield 'Invalid date interval #4' => ['123'];
    }

    /**
     * @see testValidateSucceedsWithValidDateInterval
     */
    public static function provideValidDateIntervalValues(): iterable
    {
        yield 'Valid date interval #1' => ['P2D'];
        yield 'Valid date interval #2' => ['PT2S'];
        yield 'Valid date interval #3' => ['P6YT5M'];
        yield 'Empty string' => [''];
        yield 'Null value' => [null];
    }

    /**
     * @dataProvider provideInvalidDateIntervalValues
     */
    public function testValidateFailsWithInvalidDateIntervalValues(string $dateInterval): void
    {
        $validator = new DateIntervalValidator();
        $constraint = new DateInterval();
        $violationBuilder = $this->mockConstraintViolationBuilder(DateInterval::INVALID_DATE_INTERVAL_ERROR);
        $context = $this->mockExecutionContextWithBuildViolation($constraint->message, $violationBuilder);
        $validator->initialize($context);

        $validator->validate($dateInterval, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider provideValidDateIntervalValues
     */
    public function testValidateSucceedsWithValidDateInterval(mixed $dateInterval): void
    {
        $validator = new DateIntervalValidator();
        $constraint = new DateInterval();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($dateInterval, $constraint);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new DateIntervalValidator();
        $constraint = new class() extends Constraint {
        };
        $dateInterval = 'some-date-interval';
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\DateInterval"'
        );

        $validator->validate($dateInterval, $constraint);
    }

    public function testValidateThrowsUnexpectedValueExceptionIfNumberGiven(): void
    {
        $dateInterval = 123;
        $validator = new DateIntervalValidator();
        $constraint = new DateInterval();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "int" given');

        $validator->validate($dateInterval, $constraint);
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
