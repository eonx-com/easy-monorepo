<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Traits;

use StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException;
use StepTheFkUp\EasyDecision\Tests\AbstractTestCase;
use StepTheFkUp\EasyDecision\Tests\Stubs\ValueContextAwareInputStub;

final class ContextAwareTraitTest extends AbstractTestCase
{
    /**
     * ContextAware should throw an exception when getting context before it is set.
     *
     * @return void
     */
    public function testGetContextBeforeSetException(): void
    {
        $this->expectException(ContextNotSetException::class);

        (new ValueContextAwareInputStub(10))->getContext();
    }
}
