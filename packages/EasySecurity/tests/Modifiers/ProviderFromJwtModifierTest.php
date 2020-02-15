<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Modifiers;

use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use EonX\EasySecurity\Modifiers\ProviderFromJwtModifier;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\ProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\ProviderProviderInterfaceStub;
use Symfony\Component\HttpFoundation\Request;

final class ProviderFromJwtModifierTest extends AbstractTestCase
{
    /**
     * Data provider modify.
     *
     * @return iterable<mixed>
     */
    public function modifyProvider(): iterable
    {
        yield 'No provider resolved because not token' => [
            new ProviderProviderInterfaceStub()
        ];

        $context = new Context();
        $context->setToken(new ApiKeyEasyApiToken('api-key'));

        yield 'No provider resolved because token not jwt' => [
            new ProviderProviderInterfaceStub(),
            $context
        ];

        $context->setToken(new JwtEasyApiToken([], 'jwt'));

        yield 'No provider resolved because no provider claim' => [
            new ProviderProviderInterfaceStub(),
            $context
        ];

        $context->setToken(new JwtEasyApiToken([ContextInterface::JWT_MANAGE_CLAIM => ['provider' => '']], 'jwt'));

        yield 'No provider resolved because provider claim empty' => [
            new ProviderProviderInterfaceStub(),
            $context
        ];

        $context->setToken(new JwtEasyApiToken([
            ContextInterface::JWT_MANAGE_CLAIM => ['provider' => 'provider-id']
        ], 'jwt'));

        yield 'No provider resolved because provider provider returns null' => [
            new ProviderProviderInterfaceStub(),
            $context
        ];

        $context->setToken(new JwtEasyApiToken([
            ContextInterface::JWT_MANAGE_CLAIM => ['provider' => 'provider-id']
        ], 'jwt'));

        yield 'Provider resolved' => [
            new ProviderProviderInterfaceStub($provider = new ProviderInterfaceStub('provider-id')),
            $context,
            $provider
        ];
    }

    /**
     * Test modify.
     *
     * @param \EonX\EasySecurity\Interfaces\ProviderProviderInterface $providerProvider
     * @param null|\EonX\EasySecurity\Interfaces\ContextInterface $context
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     *
     * @return void
     *
     * @dataProvider modifyProvider
     */
    public function testModify(
        ProviderProviderInterface $providerProvider,
        ?ContextInterface $context = null,
        ?ProviderInterface $provider = null
    ): void {
        $context = $context ?? new Context();

        (new ProviderFromJwtModifier($providerProvider))->modify($context, new Request());

        self::assertSame($provider, $context->getProvider());
    }
}
