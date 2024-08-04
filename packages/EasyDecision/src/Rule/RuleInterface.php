<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rule;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface RuleInterface extends HasPriorityInterface
{
    public function proceed(array $input): mixed;

    public function supports(array $input): bool;

    public function toString(): string;
}
