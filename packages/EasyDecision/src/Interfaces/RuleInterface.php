<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface RuleInterface extends HasPriorityInterface
{
    public const OUTPUT_SKIPPED = 'skipped';

    public const OUTPUT_UNSUPPORTED = 'unsupported';

    /**
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function proceed(array $input);

    /**
     * @param mixed[] $input
     */
    public function supports(array $input): bool;

    public function toString(): string;
}
