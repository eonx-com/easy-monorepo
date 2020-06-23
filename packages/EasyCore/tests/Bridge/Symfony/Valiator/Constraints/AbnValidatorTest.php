<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Valiator\Constraints;

use EoneoPay\Utils\Exceptions\UnexpectedTypeException;
use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Abn;
use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\AbnValidator;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\Validator\Constraints\AbnValidator
 */
final class AbnValidatorTest extends AbstractSymfonyTestCase
{
    /**
     * @return mixed[]
     */
    public function provideAbnWithInvalidCharacters(): array
    {
        return [
            'Starts with zero' => ['00043145470'],
            'Contains letter' => ['1234567890a'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function provideUnexpectedValues(): array
    {
        return [
            'class without __toString' => [new stdClass(), 'Expected argument of type "string", "stdClass" given'],
            'integer' => [53004085616, 'Expected argument of type "string", "integer" given'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function provideValidABN(): array
    {
        return [
            'Valid ABN #1' => ['53004085616'],
            'Valid ABN #2' => ['28043145470'],
            'Valid ABN #3' => ['53004085616'],
            'Valid ABN #4' => ['91724684688'],
            'Valid ABN #5' => ['10000000000'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function provideValidEmptyValues(): array
    {
        return [
            'Empty string' => [''],
            'Null value' => [null],
        ];
    }

    /**
     * @return string[][]
     */
    public function provideInvalidABN(): array
    {
        return [
            'Invalid ABN #1' => ['10043145470'],
            'Invalid ABN #2' => ['53004085615'],
            'Invalid ABN #3' => ['53004085615'],
            'Invalid ABN #4' => ['10000000001'],
        ];
    }

    /**
     * @dataProvider provideValidABN
     *
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
    public function testValidateSucceedsWithValidAbn(string $abn): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($abn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
    public function testValidateSucceedsWithObjectToString(): void
    {
        $class = new class() {
            public function __toString()
            {
                return '53004085616'; // Valid ABN
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
     * @dataProvider provideValidEmptyValues
     *
     * @param mixed $abn
     *
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
    public function testValidateSucceedsWithEmptyValue($abn): void
    {
        $validator = new AbnValidator();
        $constraint = new Abn();
        $context = $this->mockExecutionContextWithoutCalls();
        $validator->initialize($context);

        $validator->validate($abn, $constraint);

        $this->expectNotToPerformAssertions();
    }

    /**
     * Tests `validate` throws UnexpectedTypeException.
     *
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $validator = new AbnValidator();
        $constraint = new NotNull();
        $abn = 'some-abn';
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Unexpected type "Symfony\Component\Validator\Constraints\NotNull" found, ' .
            'expected "EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Abn"');

        $validator->validate($abn, $constraint);
    }

    /**
     * @param mixed $abn
     *
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
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

    /**
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
    public function testValidateThrowsUnexpectedValueExceptionIfObjectGiven(): void
    {
        $abn = new stdClass();
        $validator = new AbnValidator();
        $constraint = new Abn();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "stdClass" given');

        $validator->validate($abn, $constraint);
    }

    /**
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
    public function testValidateThrowsUnexpectedValueExceptionIfNumberGiven(): void
    {
        $abn = 53004085616;
        $validator = new AbnValidator();
        $constraint = new Abn();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "integer" given');

        $validator->validate($abn, $constraint);
    }

    /**
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
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
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     *
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

    /**
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     *
     * @dataProvider provideInvalidABN
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
        /** @var ConstraintViolationBuilderInterface $violationBuilder */
        $violationBuilder = $this->mock(ConstraintViolationBuilderInterface::class, static function (MockInterface $mock) use ($code): void {
            $mock
                ->shouldReceive('setParameter')
                ->once()
                ->withNoArgs()
                ->andReturnSelf();

            $mock
                ->shouldReceive('setCode')
                ->once()
                ->with($code)
                ->andReturnSelf();

            $mock
                ->shouldReceive('addViolation')
                ->once()
                ->withAnyArgs()
                ->andReturnSelf();
        });

        return $violationBuilder;
    }

    private function mockExecutionContextWithBuildViolation(
        string $message,
        ConstraintViolationBuilderInterface $violationBuilder
    ): ExecutionContextInterface {
        /** @var ExecutionContextInterface $context */
        $context = $this->mock(ExecutionContextInterface::class, static function (MockInterface $mock) use ($message, $violationBuilder): void {
            $mock
                ->shouldReceive('buildViolation')
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
            $mock
                ->shouldReceive('buildViolation')
                ->never();
        });

        return $context;
    }
}
