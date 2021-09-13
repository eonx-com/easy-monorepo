<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\External;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\External\FirebaseJwtDriver;
use EonX\EasyApiToken\Tests\AbstractTestCase;

final class FirebaseJwtDriverTest extends AbstractTestCase
{
    /**
     * @see https://github.com/firebase/php-jwt/issues/351
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testNotStringOrResourcePublicKeyException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FirebaseJwtDriver('RS256', [], 'private');
    }
}
