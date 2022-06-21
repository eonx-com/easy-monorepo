<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Data;

final class Config
{
    public function __construct(
        private readonly array $httpClientOptions,
        private readonly ?array $requestDataExtra = null,
        private readonly ?array $requestDataModifiers = null,
        private readonly ?array $requestDataModifiersWhitelist = null,
        private readonly ?bool $isRequestDataModifiersEnabled = null,
        private readonly ?bool $isEventsEnabled = null
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getHttpClientOptions(): array
    {
        return $this->httpClientOptions;
    }

    public function getRequestDataExtra(): ?array
    {
        return $this->requestDataExtra;
    }

    public function getRequestDataModifiers(): array
    {
        return $this->requestDataModifiers ?? [];
    }

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
