<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Authorization\Configurator;

use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasyApiToken\Common\ValueObject\Jwt;
use EonX\EasySecurity\Authorization\Configurator\RolesFromJwtConfigurator;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProvider;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\Common\Resolver\JwtClaimResolver;
use EonX\EasySecurity\Common\Resolver\JwtClaimResolverInterface;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtConfiguratorTest extends AbstractUnitTestCase
{
    /**
     * @see testConfigure
     */
    public static function provideConfigureData(): iterable
    {
        yield 'No role resolved because not token' => [[]];

        $context = new SecurityContext();
        $context->setToken(new ApiKey('api-key'));

        yield 'No role resolved because token not jwt' => [[], $context];

        $context->setToken(new Jwt([], 'jwt'));

        yield 'No role resolved because no roles in token' => [[], $context];

        $context->setToken(new Jwt([
            self::$mainJwtClaim => [
                'roles' => ['app:role'],
            ],
        ], 'jwt'));

        yield 'No role resolved because provider return empty array' => [[], $context];

        $context->setToken(new Jwt([
            self::$mainJwtClaim => [
                'roles' => ['app:role'],
            ],
        ], 'jwt'));

        yield 'Roles resolved' => [
            [new Role('app:role', [])],
            $context,
            [
                'app:role' => new Role('app:role', []),
            ],
            new JwtClaimResolver(),
        ];
    }

    /**
     * @param \EonX\EasySecurity\Authorization\ValueObject\Role[] $authorizationRoles
     */
    #[DataProvider('provideConfigureData')]
    public function testConfigure(
        array $authorizationRoles,
        ?SecurityContextInterface $context = null,
        ?array $roles = null,
        ?JwtClaimResolverInterface $jwtClaimFetcher = null,
    ): void {
        $context ??= new SecurityContext();
        $context->setAuthorizationMatrix(new AuthorizationMatrixProvider($authorizationRoles, []));
        $configurator = new RolesFromJwtConfigurator(self::$mainJwtClaim);

        if ($jwtClaimFetcher !== null) {
            $configurator->setJwtClaimFetcher($jwtClaimFetcher);
        }

        $configurator->configure($context, new Request());

        self::assertEquals($roles ?? [], $context->getRoles());
    }

    public function testGetPriority(): void
    {
        $configurator = new RolesFromJwtConfigurator(self::$mainJwtClaim, 15);

        self::assertSame(15, $configurator->getPriority());
    }
}
