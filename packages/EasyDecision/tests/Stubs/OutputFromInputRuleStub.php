<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

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
