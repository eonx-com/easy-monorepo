<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\Provider;

use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfig;

interface AwsCognitoJwkProviderInterface
{
    public function getJwks(UserPoolConfig $userPoolConfig): array;
}
