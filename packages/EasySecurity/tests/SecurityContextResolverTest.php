<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasySecurity\Authorization\AuthorizationMatrix;
use EonX\EasySecurity\Configurators\RolesFromJwtConfigurator;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Modifiers\ProviderFromJwtModifier;
use EonX\EasySecurity\Modifiers\RolesFromJwtModifier;
use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\SecurityContextResolver;
use EonX\EasySecurity\Tests\RolesProviders\InMemoryRolesProviderStub;
use EonX\EasySecurity\Tests\Stubs\ProviderProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\TokenDecoderStub;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

final class SecurityContextResolverTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function setModifiersProvider(): iterable
    {
        yield 'Filter non context modifier' => [
            [new stdClass()],
            [],
        ];

        yield 'Resolve successfully' => [
            [
                new RolesFromJwtModifier(new InMemoryRolesProviderStub(), static::$mainJwtClaim),
                new ProviderFromJwtModifier(new ProviderProviderInterfaceStub(), static::$mainJwtClaim),
            ],
            [
                new RolesFromJwtConfigurator(static::$mainJwtClaim),
            ],
        ];
    }

    /**
     * @param mixed[]|iterable<mixed> $modifiers
     * @param mixed[]|iterable<mixed> $configurators
     *
     * @dataProvider setModifiersProvider
     */
    public function testSetModifier(iterable $modifiers, iterable $configurators): void
    {
        $resolver = new SecurityContextResolver(
            new AuthorizationMatrix([], []),
            new SecurityContext(),
            new EasyPsr7Factory(),
            new TokenDecoderStub(),
            $modifiers,
            $configurators
        );

        self::assertInstanceOf(ContextInterface::class, $resolver->resolve(
            new Request([], [], [], [], [], ['HTTP_HOST' => 'eonx.com'])
        ));
    }
}
