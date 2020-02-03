<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Resolvers;

use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Resolvers\ProviderFromHeaderDataResolver;
use EonX\EasySecurity\Role;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\ProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\ProviderProviderInterfaceStub;
use Symfony\Component\HttpFoundation\Request;

final class ProviderFromHeaderDataResolverTest extends AbstractTestCase
{
    /**
     * Data provider for resolve tests.
     *
     * @return iterable<mixed>
     */
    public function resolveProvider(): iterable
    {
        yield 'No provider resolved because no header' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(),
            null
        ];

        yield 'No provider resolved because header empty' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(null, (new Request())->headers->set('X-Provider-Id', '')),
            null
        ];

        $request = new Request();
        $request->headers->set('X-Provider-Id', 'provider-id');

        yield 'No provider resolved because no permission' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(null, $request),
            null
        ];

        yield 'No provider resolved because provider provider returns null' => [
            new ProviderProviderInterfaceStub(),
            $this->createContextResolvingData(null, $request)->setRoles([
                new Role('app:role', ['provider:switch'])
            ]),
            null
        ];

        yield 'Provider resolved' => [
            new ProviderProviderInterfaceStub($provider = new ProviderInterfaceStub('provider-id')),
            $this->createContextResolvingData(null, $request)->setRoles([
                new Role('app:role', ['provider:switch'])
            ]),
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
        $resolver = new ProviderFromHeaderDataResolver($providerProvider);

        self::assertSame($provider, $resolver->resolve($data)->getProvider());
    }
}
