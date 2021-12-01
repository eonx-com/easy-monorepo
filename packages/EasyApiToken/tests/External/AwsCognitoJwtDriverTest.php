<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\External;

use EonX\EasyApiToken\Exceptions\MethodNotSupportedException;
use EonX\EasyApiToken\External\AwsCognito\JwkFetcher;
use EonX\EasyApiToken\External\AwsCognitoJwtDriver;
use EonX\EasyApiToken\Tests\AbstractTestCase;

final class AwsCognitoJwtDriverTest extends AbstractTestCase
{
    public function testEncodeNotSupported(): void
    {
        $this->expectException(MethodNotSupportedException::class);

        (new AwsCognitoJwtDriver(new JwkFetcher('region', 'user-pool-id')))->encode([]);
    }
}
