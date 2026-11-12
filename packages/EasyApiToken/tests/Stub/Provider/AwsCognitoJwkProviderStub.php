<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Stub\Provider;

use EonX\EasyApiToken\AwsCognito\Provider\AwsCognitoJwkProviderInterface;
use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfig;

final readonly class AwsCognitoJwkProviderStub implements AwsCognitoJwkProviderInterface
{
    /**
     * @param array<string, string> $jwks
     */
    public function __construct(
        private array $jwks,
    ) {}

    public function getJwks(UserPoolConfig $userPoolConfig): array
    {
        return $this->jwks;
    }
}
