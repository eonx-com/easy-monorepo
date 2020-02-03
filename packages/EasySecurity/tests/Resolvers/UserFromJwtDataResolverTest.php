<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Resolvers;

use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use EonX\EasySecurity\Interfaces\UserProviderInterface;
use EonX\EasySecurity\Resolvers\UserFromJwtDataResolver;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\UserInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\UserProviderInterfaceStub;
use stdClass;

final class UserFromJwtDataResolverTest extends AbstractTestCase
{
    /**
     * Data provider for resolve tests.
     *
     * @return iterable<mixed>
     */
    public function resolveProvider(): iterable
    {
        yield 'No user resolved because no token given' => [
            new UserProviderInterfaceStub(),
            $this->createContextResolvingData(),
            null
        ];

        yield 'No user resolved because token not jwt' => [
            new UserProviderInterfaceStub(),
            $this->createContextResolvingData(new ApiKeyEasyApiToken('api-key')),
            null
        ];

        yield 'No user resolved because no sub claim' => [
            new UserProviderInterfaceStub(),
            $this->createContextResolvingData(new JwtEasyApiToken([], 'jwt')),
            null
        ];

        yield 'No user resolved because sub claim empty' => [
            new UserProviderInterfaceStub(),
            $this->createContextResolvingData(new JwtEasyApiToken(['sub' => ''], 'jwt')),
            null
        ];

        yield 'No user resolved because provider returned null' => [
            new UserProviderInterfaceStub(),
            $this->createContextResolvingData(new JwtEasyApiToken(['sub' => 'user-id'], 'jwt')),
            null
        ];

        yield 'User resolved' => [
            new UserProviderInterfaceStub($user = new UserInterfaceStub('user-id')),
            $this->createContextResolvingData(new JwtEasyApiToken([
                'sub' => 'user-id',
                ContextInterface::JWT_MANAGE_CLAIM => new stdClass() // To cover getClaimSafely
            ], 'jwt')),
            $user
        ];
    }

    /**
     * Test UserFromJwtDataResolver.
     *
     * @param \EonX\EasySecurity\Interfaces\UserProviderInterface $userProvider
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     * @param null|\EonX\EasySecurity\Interfaces\UserInterface $user
     *
     * @return void
     *
     * @dataProvider resolveProvider
     */
    public function testResolve(
        UserProviderInterface $userProvider,
        ContextResolvingDataInterface $data,
        ?UserInterface $user = null
    ): void {
        $resolver = new UserFromJwtDataResolver($userProvider);

        self::assertSame($user, $resolver->resolve($data)->getUser());
        self::assertEquals(0, $resolver->getPriority());
    }
}
