<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\ContextAwareInterface;
use EonX\EasyDecision\Traits\ContextAwareTrait;

final class RuleStopPropagationStub extends RuleStub implements ContextAwareInterface
{
    use ContextAwareTrait;

    /**
     * Stop propagation.
     *
     * @param mixed[] $input
     */
    public function proceed(array $input): mixed
    {
        $this->context->stopPropagation();

        return parent::proceed($input);
    }
}
