<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface RuleInterface
{
    public const OUTPUT_SKIPPED = 'skipped';

    public const OUTPUT_UNSUPPORTED = 'unsupported';

    public function getPriority(): int;

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
