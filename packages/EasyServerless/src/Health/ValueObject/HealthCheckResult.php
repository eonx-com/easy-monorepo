<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Health\ValueObject;

use \JsonSerializable;

final readonly class HealthCheckResult implements JsonSerializable
{
    public function __construct(
        private bool $isHealthy,
        private ?string $reason = null
    ) {
    }

    public function isHealthy(): bool
    {
        return $this->isHealthy;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function jsonSerialize(): array
    {
        return [
            'isHealthy' => $this->isHealthy,
            'reason' => $this->reason,
        ];
    }
}
