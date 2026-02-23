<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\ValueObject;

final readonly class HashedApiKey implements ApiTokenInterface
{
    public const string DEFAULT_VERSION = 'v1';

    public const string KEY_ID = 'id';

    public const string KEY_SECRET = 'secret';

    public const string KEY_VERSION = 'version';

    public function __construct(
        private int|string $id,
        private string $secret,
        private string $original,
        private ?string $version = null,
    ) {
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getOriginalToken(): string
    {
        return $this->original;
    }

    public function getPayload(): array
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_SECRET => $this->secret,
            self::KEY_VERSION => $this->version,
        ];
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getVersion(): string
    {
        return $this->version ?? self::DEFAULT_VERSION;
    }
}
