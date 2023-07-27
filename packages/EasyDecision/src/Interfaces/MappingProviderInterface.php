<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface MappingProviderInterface
{
    public function getDecisionType(string $name): string;
}
