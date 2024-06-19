<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasyApiToken\Common\ValueObject\Jwt;
use EonX\EasySecurity\JwtClaimFetcher;

final class JwtClaimFetcherTest extends AbstractTestCase
{
    public function testExceptionReturnsDefault(): void
    {
        $jwtClaimFetcher = new JwtClaimFetcher();

        self::assertEquals('default', $jwtClaimFetcher->getClaim(new Jwt([], 'original'), 'claim', 'default'));
    }
}
