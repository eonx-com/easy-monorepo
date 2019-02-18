<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Tests\Stubs;

use StepTheFkUp\MockBuilder\AbstractMockBuilder;

/**
 * @method self hasMethodOne(string $param1, int $param2)
 * @method self hasMethodTwo(string $param1, int $param2)
 * @method self hasMethodThree(object $object)
 *
 * @see \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub
 */
final class ClassMockBuilderStub extends AbstractMockBuilder
{
    /**
     * Get class to mock.
     *
     * @return string
     */
    protected function getClassToMock(): string
    {
        return ClassStub::class;
    }
}
