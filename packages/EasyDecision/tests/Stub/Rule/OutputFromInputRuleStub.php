<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Rule;

use EonX\EasyDecision\Rule\RuleInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

final class OutputFromInputRuleStub implements RuleInterface
{
    use HasPriorityTrait;

    public function proceed(array $input): bool
    {
        return $input['output'];
    }

    public function supports(array $input): bool
    {
        return isset($input['output']);
    }

    public function toString(): string
    {
        return 'output_from_input';
    }
}
