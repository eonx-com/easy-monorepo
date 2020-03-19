<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Modifiers;

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\ContextResolver;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Modifiers\ProviderFromJwtModifier;
use EonX\EasySecurity\Modifiers\RolesFromJwtModifier;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\RolesProviders\InMemoryRolesProviderStub;
use EonX\EasySecurity\Tests\Stubs\ProviderProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\TokenDecoderStub;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

final class ContextResolverTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function setModifiersProvider(): iterable
    {
        yield 'Filter non context modifier' => [
            [new stdClass()],
        ];

        yield 'Resolve successfully' => [
            [
                new RolesFromJwtModifier(new InMemoryRolesProviderStub(), static::$mainJwtClaim),
                new ProviderFromJwtModifier(new ProviderProviderInterfaceStub(), static::$mainJwtClaim),
            ],
        ];
    }

    /**
     * @param mixed[]|iterable<mixed> $modifiers
     *
     * @dataProvider setModifiersProvider
     */
    public function testSetModifier(iterable $modifiers): void
    {
        $resolver = new ContextResolver(
            new Context(),
            new EasyPsr7Factory(),
            new TokenDecoderStub(),
            $modifiers
        );

        self::assertInstanceOf(ContextInterface::class, $resolver->resolve(
            new Request([], [], [], [], [], ['HTTP_HOST' => 'eonx.com'])
        ));
    }
}
