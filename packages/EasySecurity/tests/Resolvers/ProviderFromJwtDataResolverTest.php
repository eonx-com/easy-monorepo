<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Resolvers;

use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Resolvers\ProviderFromJwtDataResolver;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\ProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\ProviderProviderInterfaceStub;

final class ProviderFromJwtDataResolverTest extends AbstractTestCase
{
    /**
     * Data provider resolver.
     *
     * @return iterable<mixed>
     */
    public function resolveProvider(): iterable
    {
        yield 'No provider resolved because not token' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(),
            null
        ];

        yield 'No provider resolved because token not jwt' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(new ApiKeyEasyApiToken('api-key')),
            null
        ];

        yield 'No provider resolved because no provider claim' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(new JwtEasyApiToken([], 'jwt')),
            null
        ];

        yield 'No provider resolved because provider claim empty' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(new JwtEasyApiToken([
                ContextInterface::JWT_MANAGE_CLAIM => ['provider' => '']
            ], 'jwt')),
            null
        ];

        yield 'No provider resolved because provider provider returns null' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(new JwtEasyApiToken([
                ContextInterface::JWT_MANAGE_CLAIM => ['provider' => 'provider-id']
            ], 'jwt')),
            null
        ];

        yield 'Provider resolved' => [
            new ProviderProviderInterfaceStub($provider = new ProviderInterfaceStub('provider-id')),
            $this->createContextResolvingData(new JwtEasyApiToken([
                ContextInterface::JWT_MANAGE_CLAIM => ['provider' => 'provider-id']
            ], 'jwt')),
            $provider
        ];
    }

    /**
     * Test resolve.
     *
     * @param \EonX\EasySecurity\Interfaces\ProviderProviderInterface $providerProvider
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     *
     * @return void
     *
     * @dataProvider resolveProvider
     */
    public function testResolve(
        ProviderProviderInterface $providerProvider,
        ContextResolvingDataInterface $data,
        ?ProviderInterface $provider = null
    ): void {
        $resolver = new ProviderFromJwtDataResolver($providerProvider);

        self::assertSame($provider, $resolver->resolve($data)->getProvider());
    }
}
