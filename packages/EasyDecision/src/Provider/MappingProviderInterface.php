<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Provider;

interface MappingProviderInterface
{
    public function getDecisionType(string $name): string;
}
