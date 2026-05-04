<?php
declare(strict_types=1);

namespace EonX\EasyServerless\AppMetric\ValueObject;

final readonly class AppMetric implements AppMetricInterface
{
    public function __construct(
        private string $name,
        private ?array $dimensions = null,
        private ?string $namespace = null,
    ) {
    }

    public static function create(
        string $name,
        ?array $dimensions = null,
        ?string $namespace = null,
    ): self {
        return new self($name, $dimensions, $namespace);
    }

    public function getDimensions(): array
    {
        return $this->dimensions ?? [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
}
