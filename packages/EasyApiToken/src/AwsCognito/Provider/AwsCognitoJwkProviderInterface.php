<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\Provider;

use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfigInterface;

interface AwsCognitoJwkProviderInterface
{
    public function getJwks(UserPoolConfigInterface $userPoolConfig): array;
}
