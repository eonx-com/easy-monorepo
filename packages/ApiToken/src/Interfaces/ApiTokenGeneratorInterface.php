<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces;

interface ApiTokenGeneratorInterface
{
    /**
     * Generate API token for given payload.
     *
     * @param mixed[] $payload
     *
     * @return string
     */
    public function generate(array $payload): string;
}