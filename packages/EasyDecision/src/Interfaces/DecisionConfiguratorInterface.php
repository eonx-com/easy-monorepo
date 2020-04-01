<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionConfiguratorInterface
{
    public function configure(DecisionInterface $decision): void;

    public function getPriority(): int;
}
