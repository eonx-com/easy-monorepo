<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Tests;

use BadMethodCallException;
use Exception;
use Mockery\MockInterface;
use stdClass;
use StepTheFkUp\MockBuilder\Tests\Stubs\ClassMockBuilderStub;
use StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub;

/**
 * @covers \StepTheFkUp\MockBuilder\AbstractMockBuilder
 * @covers \StepTheFkUp\MockBuilder\Returns\ReturnSelf
 * @covers \StepTheFkUp\MockBuilder\Returns\ReturnValue
 *
 * @runTestsInSeparateProcesses
 */
final class AbstractMockBuilderTest extends AbstractTestCase
{
    /**
     * Test add custom mock configuration by closure.
     *
     * @return void
     */
    public function testAddConfiguration(): void
    {
        $expectedObject = new stdClass();
        $mockBuilder = (new ClassMockBuilderStub())
            ->addConfiguration(function (MockInterface $mock) use ($expectedObject): void {
                $mock->shouldReceive('methodOne')->once()
                    ->with('1', 2)->andReturn($expectedObject);
            });

        // Build mock based on configuration and type hint to correct object using doc block.
        /** @var \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub $classStub */
        $classStub = $mockBuilder->build();

        // Call expected method from mocked object.
        $actual = $classStub->methodOne('1', 2);

        $this->assertEquals($expectedObject, $actual);
    }

    /**
     * Test __call adds configuration properly and can return expected object.
     *
     * @return void
     */
    public function testMagicMethodCallWithChainedAndReturn(): void
    {
        $expectedObject = new stdClass();
        $mockBuilder = (new ClassMockBuilderStub())->hasMethodOne('1', 2)
            ->andReturn($expectedObject);

        /** @var \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub $classStub */
        $classStub = $mockBuilder->build();

        // Call expected method from mocked object.
        $actual = $classStub->methodOne('1', 2);

        $this->assertEquals($expectedObject, $actual);
    }

    /**
     * Test __call adds configuration properly and can throw exception.
     *
     * @return void
     */
    public function testMagicMethodCallWithChainedAndThrow(): void
    {
        $this->expectException(Exception::class);

        $mockBuilder = (new ClassMockBuilderStub())->hasMethodOne('1', 2)
            ->andThrow(Exception::class);

        /** @var \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub $classStub */
        $classStub = $mockBuilder->build();

        // Call expected method from mocked object based on times.
        $this->assertNull($classStub->methodOne('1', 2));
    }

    /**
     * Test __call adds configuration properly and can call times to override expected call count.
     *
     * @return void
     */
    public function testMagicMethodCallWithChainedTimes(): void
    {
        $mockBuilder = (new ClassMockBuilderStub())->hasMethodOne('1', 2)
            ->times(3);

        /** @var \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub $classStub */
        $classStub = $mockBuilder->build();

        // Call expected method from mocked object based on times.
        $this->assertNull($classStub->methodOne('1', 2));
        $this->assertNull($classStub->methodOne('1', 2));
        $this->assertNull($classStub->methodOne('1', 2));
    }

    /**
     * Test __call adds configuration that uses closure for expected arguments.
     * This is commonly used when object being asserted is not in the test context.
     *
     * @return void
     */
    public function testMagicMethodCallWithClosureToAssertObject(): void
    {
        $mockBuilder = (new ClassMockBuilderStub())
            ->hasMethodThree(function ($objectToTest): bool {
                return $objectToTest->propOne === 'ABC'
                    && $objectToTest->propTwo === 1234;
            })
            ->andReturn(true);

        /** @var \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub $classStub */
        $classStub = $mockBuilder->build();

        $objectPassed = new stdClass();
        $objectPassed->propOne = 'ABC';
        $objectPassed->propTwo = 1234;

        // Call expected method from mocked object.
        $this->assertTrue($classStub->methodThree($objectPassed));
    }

    /**
     * Test __call throws BadMethodCallException if method does not exist in `\get_class_methods(class_to_mock)`
     *
     * @return void
     */
    public function testMagicMethodCallWithPrivateMethodThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Undefined/Invalid expected method `cantMockthis`'
            . ' in class `StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub`.'
            . ' Available methods: `methodOne`, `methodThree`, `methodTwo`.'
        );

        /** @noinspection PhpUndefinedMethodInspection */
        (new ClassMockBuilderStub())->hasCantMockthis('something');
    }

    /**
     * Test __call throws BadMethodCallException if method does not exist in `\get_class_methods(class_to_mock)`
     *
     * @return void
     */
    public function testMagicMethodCallWithoutHasExpectation(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method expectation `methodTwo` not allowed. Should be `hasMethodTwo`.');

        /** @noinspection PhpUndefinedMethodInspection */
        (new ClassMockBuilderStub())->methodTwo(1, 2);
    }

    /**
     * Test mock object returns self. To be used in method chaining.
     *
     * @return void
     */
    public function testMockedObjectReturnsSelf(): void
    {
        $mockBuilder = (new ClassMockBuilderStub())->hasMethodTwo('1', 2)
            ->andReturnSelf();

        /** @var \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub $classStub */
        $classStub = $mockBuilder->build();

        // Call expected method from mocked object and return itself.
        $this->assertSame($classStub, $classStub->methodTwo('1', 2));
    }

    /**
     * Test builder accepts instance in constructor to allow partial mocking.
     *
     * @return void
     */
    public function testPartialMocking(): void
    {
        $classStubOriginal = new ClassStub();

        $mockBuilder = (new ClassMockBuilderStub($classStubOriginal))
            ->hasMethodTwo('1', 2)
            ->andReturnSelf();

        /** @var \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub $classStub */
        $classStub = $mockBuilder->build();

        // Call expected method from mocked object and return itself.
        $this->assertSame($classStubOriginal, $classStub->methodTwo('1', 2));
        // Can still call other methods.
        $this->assertInstanceOf(stdClass::class, $classStub->methodOne('1', 2));
    }
}
