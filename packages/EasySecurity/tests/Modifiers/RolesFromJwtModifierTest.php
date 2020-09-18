<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Modifiers;

use EonX\EasyApiToken\Tokens\ApiKey;
use EonX\EasyApiToken\Tokens\Jwt;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;
use EonX\EasySecurity\Modifiers\RolesFromJwtModifier;
use EonX\EasySecurity\Role;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\RolesProviders\InMemoryRolesProviderStub;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtModifierTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function modifyProvider(): iterable
    {
        yield 'No role resolved because not token' => [new InMemoryRolesProviderStub()];

        $context = new Context();
        $context->setToken(new ApiKey('api-key'));

        yield 'No role resolved because token not jwt' => [new InMemoryRolesProviderStub(), $context];

        $context->setToken(new Jwt([], 'jwt'));

        yield 'No role resolved because no roles in token' => [new InMemoryRolesProviderStub(), $context];

        $context->setToken(new Jwt([
            static::$mainJwtClaim => [
                'roles' => ['app:role'],
            ],
        ], 'jwt'));

        yield 'No role resolved because provider return empty array' => [
            new InMemoryRolesProviderStub(),
            $context,
        ];

        $context->setToken(new Jwt([
            static::$mainJwtClaim => [
                'roles' => ['app:role'],
            ],
        ], 'jwt'));

        yield 'Roles resolved' => [
            new InMemoryRolesProviderStub([new Role('app:role', [])]),
            $context,
            ['app:role' => new Role('app:role', [])],
        ];
    }

    /**
     * @param null|mixed[] $roles
     *
     * @dataProvider modifyProvider
     */
    public function testModify(
        RolesProviderInterface $rolesProvider,
        ?ContextInterface $context = null,
        ?array $roles = null
    ): void {
        $context = $context ?? new Context();

        (new RolesFromJwtModifier($rolesProvider, static::$mainJwtClaim))->modify($context, new Request());

        self::assertEquals($roles ?? [], $context->getRoles());
    }
}
