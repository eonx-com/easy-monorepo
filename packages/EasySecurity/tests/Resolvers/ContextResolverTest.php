<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Resolvers;

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Resolvers\ContextResolver;
use EonX\EasySecurity\Resolvers\ProviderFromJwtDataResolver;
use EonX\EasySecurity\Resolvers\RolesFromJwtDataResolver;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\RolesProviders\InMemoryRolesProviderStub;
use EonX\EasySecurity\Tests\Stubs\ContextFactoryInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\ProviderProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\TokenDecoderStub;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

final class ContextResolverTest extends AbstractTestCase
{
    /**
     * Data provider for setResolvers tests.
     *
     * @return iterable<mixed>
     */
    public function setResolversProvider(): iterable
    {
        yield 'Filter non data resolver' => [
            [new stdClass()]
        ];

        yield 'Resolve successfully' => [
            [
                new RolesFromJwtDataResolver(new InMemoryRolesProviderStub()),
                new ProviderFromJwtDataResolver(new ProviderProviderInterfaceStub())
            ]
        ];
    }

    /**
     * Test setResolvers.
     *
     * @param mixed[]|iterable<mixed> $resolvers
     *
     * @return void
     *
     * @dataProvider setResolversProvider
     */
    public function testSetResolvers(iterable $resolvers): void
    {
        $resolver = new ContextResolver(
            new ContextFactoryInterfaceStub(),
            new EasyPsr7Factory(),
            new TokenDecoderStub(),
            $resolvers
        );

        self::assertInstanceOf(ContextInterface::class, $resolver->resolve(
            new Request([], [], [], [], [], ['HTTP_HOST' => 'google.com'])
        ));
    }
}
