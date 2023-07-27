<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\External;

use EonX\EasyApiToken\Exceptions\MethodNotSupportedException;
use EonX\EasyApiToken\External\AwsCognito\UserPoolConfig;
use EonX\EasyApiToken\External\AwsCognitoJwtDriver;
use EonX\EasyApiToken\Tests\AbstractTestCase;

final class AwsCognitoJwtDriverTest extends AbstractTestCase
{
    public function testEncodeNotSupported(): void
    {
        $this->expectException(MethodNotSupportedException::class);

        (new AwsCognitoJwtDriver(new UserPoolConfig('app', 'region', 'user-pool-id')))->encode([]);
    }
}
