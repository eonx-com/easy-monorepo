<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasyApiToken\Tokens\JwtEasyApiToken;
use EonX\EasySecurity\JwtClaimFetcher;

final class JwtClaimFetcherTest extends AbstractTestCase
{
    public function testExceptionReturnsDefault(): void
    {
        $jwtClaimFetcher = new JwtClaimFetcher();

        self::assertEquals(
            'default',
            $jwtClaimFetcher->getClaim(new JwtEasyApiToken([], 'original'), 'claim', 'default')
        );
    }
}
