<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\DependencyInjection;

use EonX\EasySecurity\Bridge\Symfony\Security\Voters\PermissionVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\ProviderVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\RoleVoter;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class EnableVotersTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testVotersEnabled
     */
    public static function providerTestVotersEnabled(): iterable
    {
        yield 'All disabled by default' => [
            [],
            [
                PermissionVoter::class => false,
                ProviderVoter::class => false,
                RoleVoter::class => false,
            ],
        ];

        yield 'Permission only implicit' => [
            [__DIR__ . '/../Fixtures/config/voters/permission_only_implicit.yaml'],
            [
                PermissionVoter::class => true,
                ProviderVoter::class => false,
                RoleVoter::class => false,
            ],
        ];

        yield 'Provider only implicit' => [
            [__DIR__ . '/../Fixtures/config/voters/provider_only_implicit.yaml'],
            [
                PermissionVoter::class => false,
                ProviderVoter::class => true,
                RoleVoter::class => false,
            ],
        ];

        yield 'Role only implicit' => [
            [__DIR__ . '/../Fixtures/config/voters/role_only_implicit.yaml'],
            [
                PermissionVoter::class => false,
                ProviderVoter::class => false,
                RoleVoter::class => true,
            ],
        ];

        yield 'All enabled explicit' => [
            [__DIR__ . '/../Fixtures/config/voters/all_enabled_explicit.yaml'],
            [
                PermissionVoter::class => true,
                ProviderVoter::class => true,
                RoleVoter::class => true,
            ],
        ];
    }

    /**
     * @param string[] $configs
     * @param mixed[] $assertions
     *
     * @dataProvider providerTestVotersEnabled
     */
    public function testVotersEnabled(array $configs, array $assertions): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        foreach ($assertions as $id => $bool) {
            self::assertEquals($bool, $container->has($id));
        }
    }
}
