<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\ValueObject;

interface HashedApiKeyInterface extends ApiTokenInterface
{
    final public const DEFAULT_VERSION = 'v1';

    final public const KEY_ID = 'id';

    final public const KEY_SECRET = 'secret';

    final public const KEY_VERSION = 'version';

    public function getId(): int|string;

    public function getSecret(): string;

    public function getVersion(): string;
}
