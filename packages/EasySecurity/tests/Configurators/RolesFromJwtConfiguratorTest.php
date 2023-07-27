<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Configurators;

use EonX\EasyApiToken\Tokens\ApiKey;
use EonX\EasyApiToken\Tokens\Jwt;
use EonX\EasySecurity\Authorization\AuthorizationMatrix;
use EonX\EasySecurity\Authorization\Role;
use EonX\EasySecurity\Configurators\RolesFromJwtConfigurator;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\JwtClaimFetcher;
use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtConfiguratorTest extends AbstractTestCase
{
    /**
     * @see testConfigure
     */
    public static function providerTestConfigure(): iterable
    {
        yield 'No role resolved because not token' => [[]];

        $context = new SecurityContext();
        $context->setToken(new ApiKey('api-key'));

        yield 'No role resolved because token not jwt' => [[], $context];

        $context->setToken(new Jwt([], 'jwt'));

        yield 'No role resolved because no roles in token' => [[], $context];

        $context->setToken(new Jwt([
            static::$mainJwtClaim => [
                'roles' => ['app:role'],
            ],
        ], 'jwt'));

        yield 'No role resolved because provider return empty array' => [[], $context];

        $context->setToken(new Jwt([
            static::$mainJwtClaim => [
                'roles' => ['app:role'],
            ],
        ], 'jwt'));

        yield 'Roles resolved' => [
            [new Role('app:role', [])],
            $context,
            [
                'app:role' => new Role('app:role', []),
            ],
            new JwtClaimFetcher(),
        ];
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[] $authorizationRoles
     *
     * @dataProvider providerTestConfigure
     */
    public function testConfigure(
        array $authorizationRoles,
        ?SecurityContextInterface $context = null,
        ?array $roles = null,
        ?JwtClaimFetcherInterface $jwtClaimFetcher = null,
    ): void {
        $context ??= new SecurityContext();
        $context->setAuthorizationMatrix(new AuthorizationMatrix($authorizationRoles, []));
        $configurator = new RolesFromJwtConfigurator(static::$mainJwtClaim);

        if ($jwtClaimFetcher !== null) {
            $configurator->setJwtClaimFetcher($jwtClaimFetcher);
        }

        $configurator->configure($context, new Request());

        self::assertEquals($roles ?? [], $context->getRoles());
    }

    public function testGetPriority(): void
    {
        $configurator = new RolesFromJwtConfigurator(static::$mainJwtClaim, 15);

        self::assertEquals(15, $configurator->getPriority());
    }
}
