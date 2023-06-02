<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\DateInterval;
use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\DateIntervalValidator;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\Validator\Constraints\DateIntervalValidator
 */
final class DateIntervalValidatorTest extends AbstractSymfonyTestCase
{
    /**
     * @return string[][]
     *
     * @see testValidateFailsWithInvalidDateIntervalValues
     */
    public function provideInvalidDateIntervalValues(): array
    {
        return [
            'Invalid date interval #1' => ['P4D1Y'],
            'Invalid date interval #2' => ['6MT'],
            'Invalid date interval #3' => ['abc'],
            'Invalid date interval #4' => ['123'],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testValidateSucceedsWithValidDateInterval
     */
    public function provideValidDateIntervalValues(): array
    {
        return [
            'Valid date interval #1' => ['P2D'],
            'Valid date interval #2' => ['PT2S'],
            'Valid date interval #3' => ['P6YT5M'],
            'Empty string' => [''],
            'Null value' => [null],
        ];
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
     * @param mixed $dateInterval
     *
     * @dataProvider provideValidDateIntervalValues
     */
    public function testValidateSucceedsWithValidDateInterval($dateInterval): void
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
            'Expected argument of type "EonX\EasyCore\Bridge\Symfony\Validator\Constraints\DateInterval"',
        );

        $validator->validate($dateInterval, $constraint);
    }

    public function testValidateThrowsUnexpectedValueExceptionIfNumberGiven(): void
    {
        $dateInterval = 53004085616;
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
