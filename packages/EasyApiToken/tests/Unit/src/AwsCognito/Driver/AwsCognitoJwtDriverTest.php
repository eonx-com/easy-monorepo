<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\AwsCognito\Driver;

use EonX\EasyApiToken\AwsCognito\Driver\AwsCognitoJwtDriver;
use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfig;
use EonX\EasyApiToken\Common\Exception\MethodNotSupportedException;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;

final class AwsCognitoJwtDriverTest extends AbstractUnitTestCase
{
    public function testEncodeNotSupported(): void
    {
        $this->expectException(MethodNotSupportedException::class);

        (new AwsCognitoJwtDriver(new UserPoolConfig('app', 'region', 'user-pool-id')))->encode([]);
    }
}
