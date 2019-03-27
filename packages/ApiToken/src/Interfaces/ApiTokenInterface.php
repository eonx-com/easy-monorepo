<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces;

interface ApiTokenInterface
{
    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array;
}
