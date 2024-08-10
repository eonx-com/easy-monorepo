<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Common\Resolver;

use EonX\EasyApiToken\Common\ValueObject\JwtToken;
use EonX\EasySecurity\Common\Resolver\JwtClaimResolver;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;

final class JwtClaimResolverTest extends AbstractUnitTestCase
{
    public function testExceptionReturnsDefault(): void
    {
        $jwtClaimFetcher = new JwtClaimResolver();

        self::assertEquals('default', $jwtClaimFetcher->getClaim(new JwtToken([], 'original'), 'claim', 'default'));
    }
}
