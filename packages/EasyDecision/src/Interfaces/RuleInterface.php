<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface RuleInterface extends HasPriorityInterface
{
    public const OUTPUT_SKIPPED = 'skipped';

    public const OUTPUT_UNSUPPORTED = 'unsupported';

    public function proceed(array $input): mixed;

    public function supports(array $input): bool;

    public function toString(): string;
}
