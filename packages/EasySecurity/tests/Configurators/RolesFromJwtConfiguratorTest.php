<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Configurators;

use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;
use EonX\EasySecurity\Configurators\RolesFromJwtConfigurator;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\JwtClaimFetcher;
use EonX\EasySecurity\Role;
use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\RolesProviders\InMemoryRolesProviderStub;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtConfiguratorTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestConfigure(): iterable
    {
        yield 'No role resolved because not token' => [
            new InMemoryRolesProviderStub(),
        ];

        $context = new SecurityContext();
        $context->setToken(new ApiKeyEasyApiToken('api-key'));

        yield 'No role resolved because token not jwt' => [
            new InMemoryRolesProviderStub(),
            $context,
        ];

        $context->setToken(new JwtEasyApiToken([], 'jwt'));

        yield 'No role resolved because no roles in token' => [
            new InMemoryRolesProviderStub(),
            $context,
        ];

        $context->setToken(new JwtEasyApiToken([
            static::$mainJwtClaim => ['roles' => ['app:role']],
        ], 'jwt'));

        yield 'No role resolved because provider return empty array' => [
            new InMemoryRolesProviderStub(),
            $context,
        ];

        $context->setToken(new JwtEasyApiToken([
            static::$mainJwtClaim => ['roles' => ['app:role']],
        ], 'jwt'));

        yield 'Roles resolved' => [
            new InMemoryRolesProviderStub([new Role('app:role', [])]),
            $context,
            ['app:role' => new Role('app:role', [])],
            new JwtClaimFetcher(),
        ];
    }

    /**
     * @param null|mixed[] $roles
     *
     * @dataProvider providerTestConfigure
     */
    public function testConfigure(
        RolesProviderInterface $rolesProvider,
        ?SecurityContextInterface $context = null,
        ?array $roles = null,
        ?JwtClaimFetcherInterface $jwtClaimFetcher = null
    ): void {
        $context = $context ?? new SecurityContext();
        $configurator = new RolesFromJwtConfigurator(static::$mainJwtClaim, $rolesProvider);

        if ($jwtClaimFetcher !== null) {
            $configurator->setJwtClaimFetcher($jwtClaimFetcher);
        }

        $configurator->configure($context, new Request());

        self::assertEquals($roles ?? [], $context->getRoles());
    }

    public function testGetPriority(): void
    {
        $configurator = new RolesFromJwtConfigurator(static::$mainJwtClaim, new InMemoryRolesProviderStub(), 15);

        self::assertEquals(15, $configurator->getPriority());
    }
}
