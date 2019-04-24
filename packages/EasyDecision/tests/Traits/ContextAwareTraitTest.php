<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Traits;

use LoyaltyCorp\EasyDecision\Exceptions\ContextNotSetException;
use LoyaltyCorp\EasyDecision\Tests\AbstractTestCase;
use LoyaltyCorp\EasyDecision\Tests\Stubs\ValueContextAwareInputStub;

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

\class_alias(
    ContextAwareTraitTest::class,
    'StepTheFkUp\EasyDecision\Tests\Traits\ContextAwareTraitTest',
    false
);
