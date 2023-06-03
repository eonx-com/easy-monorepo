<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens;

use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyInterface;

final class HashedApiKey implements HashedApiKeyInterface
{
    public function __construct(
        private int|string $id,
        private string $secret,
        private string $original,
        private ?string $version = null,
    ) {
    }

    public function getOriginalToken(): string
    {
        return $this->original;
    }

    /**
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_SECRET => $this->secret,
            self::KEY_VERSION => $this->version,
        ];
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getVersion(): string
    {
        return $this->version ?? self::DEFAULT_VERSION;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}
