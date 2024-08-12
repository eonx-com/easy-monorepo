<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Common\Resolver;

use EonX\EasyApiToken\Common\ValueObject\Jwt;
use EonX\EasySecurity\Common\Resolver\JwtClaimResolver;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;

final class JwtClaimResolverTest extends AbstractUnitTestCase
{
    public function testExceptionReturnsDefault(): void
    {
        $jwtClaimFetcher = new JwtClaimResolver();

        self::assertSame('default', $jwtClaimFetcher->getClaim(new Jwt([], 'original'), 'claim', 'default'));
    }
}
