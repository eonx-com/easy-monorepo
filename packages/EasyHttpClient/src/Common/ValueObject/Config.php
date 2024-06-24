<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\ValueObject;

final readonly class Config
{
    /**
     * @param \EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface[]|null $requestDataModifiers
     * @param string[]|null $requestDataModifiersWhitelist
     */
    public function __construct(
        private array $httpClientOptions,
        private ?array $requestDataExtra = null,
        private ?array $requestDataModifiers = null,
        private ?array $requestDataModifiersWhitelist = null,
        private ?bool $isRequestDataModifiersEnabled = null,
        private ?bool $isEventsEnabled = null,
    ) {
    }

    public function getHttpClientOptions(): array
    {
        return $this->httpClientOptions;
    }

    public function getRequestDataExtra(): ?array
    {
        return $this->requestDataExtra;
    }

    /**
     * @return \EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface[]
     */
    public function getRequestDataModifiers(): array
    {
        return $this->requestDataModifiers ?? [];
    }

    /**
     * @return string[]
     */
    public function getRequestDataModifiersWhitelist(): array
    {
        return $this->requestDataModifiersWhitelist ?? [];
    }

    public function isEventsEnabled(): bool
    {
        return $this->isEventsEnabled ?? true;
    }

    public function isRequestDataModifiersEnabled(): bool
    {
        return $this->isRequestDataModifiersEnabled ?? true;
    }
}
