<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Rule;

use EonX\EasyDecision\Context\ContextAwareInterface;
use EonX\EasyDecision\Context\ContextAwareTrait;

final class RuleStopPropagationStub extends RuleStub implements ContextAwareInterface
{
    use ContextAwareTrait;

    /**
     * Stop propagation.
     */
    public function proceed(array $input): mixed
    {
        $this->context->stopPropagation();

        return parent::proceed($input);
    }
}
