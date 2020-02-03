<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Resolvers;

use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;
use EonX\EasySecurity\Resolvers\RolesFromJwtDataResolver;
use EonX\EasySecurity\Role;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\RolesProviders\InMemoryRolesProviderStub;

final class RolesFromJwtDataResolverTest extends AbstractTestCase
{
    /**
     * Data provider for resolve tests.
     *
     * @return iterable<mixed>
     */
    public function resolveProvider(): iterable
    {
        yield 'No role resolved because not token' => [
            new InMemoryRolesProviderStub(),
            $this->createContextResolvingData(),
            null
        ];

        yield 'No role resolved because token not jwt' => [
            new InMemoryRolesProviderStub(),
            $this->createContextResolvingData(new ApiKeyEasyApiToken('api-key')),
            null
        ];

        yield 'No role resolved because no roles in token' => [
            new InMemoryRolesProviderStub(),
            $this->createContextResolvingData(new JwtEasyApiToken([], 'jwt')),
            null
        ];

        yield 'No role resolved because provider return empty array' => [
            new InMemoryRolesProviderStub(),
            $this->createContextResolvingData(new JwtEasyApiToken([
                ContextInterface::JWT_MANAGE_CLAIM => ['roles' => ['app:role']]
            ], 'jwt')),
            null
        ];

        yield 'Roles resolved' => [
            new InMemoryRolesProviderStub([new Role('app:role', [])]),
            $this->createContextResolvingData(new JwtEasyApiToken([
                ContextInterface::JWT_MANAGE_CLAIM => ['roles' => ['app:role']]
            ], 'jwt')),
            [new Role('app:role', [])]
        ];
    }

    /**
     * Test resolve.
     *
     * @param \EonX\EasySecurity\Interfaces\RolesProviderInterface $rolesProvider
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     * @param null|mixed[] $roles
     *
     * @return void
     *
     * @dataProvider resolveProvider
     */
    public function testResolve(
        RolesProviderInterface $rolesProvider,
        ContextResolvingDataInterface $data,
        ?array $roles = null
    ): void {
        $resolver = new RolesFromJwtDataResolver($rolesProvider);

        self::assertEquals($roles, $resolver->resolve($data)->getRoles());
    }
}
