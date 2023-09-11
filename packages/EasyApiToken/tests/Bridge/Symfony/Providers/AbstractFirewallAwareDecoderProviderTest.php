<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony\Providers;

use EonX\EasyApiToken\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs\FirewallAwareDecoderProviderStub;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class AbstractFirewallAwareDecoderProviderTest extends AbstractSymfonyTestCase
{
    /**
     * @see testDecoderProvider
     */
    public static function providerTestDecoderProvider(): iterable
    {
        yield 'Null firewall' => [null];

        $request = new Request();
        $request->attributes->set('_firewall_context', 'firewall_context');

        yield 'Default firewall from container' => [
            'my-firewall',
            [
                __DIR__ . '/../Fixtures/config/default_firewall_context.yaml',
            ],
            [
                'firewall_context' => true,
            ],
            $request,
        ];
    }

    /**
     * @param string[]|null $configs
     * @param string[]|null $firewallMap
     */
    #[DataProvider('providerTestDecoderProvider')]
    public function testDecoderProvider(
        ?string $expectedFirewall,
        ?array $configs = null,
        ?array $firewallMap = null,
        ?Request $request = null,
    ): void {
        $kernel = $this->getKernel($configs);
        $container = $kernel->getContainer();
        $requestStack = new RequestStack();
        if ($request !== null) {
            $requestStack->push($request);
        }
        $provider = new FirewallAwareDecoderProviderStub();

        $provider->setFirewallMap(new FirewallMap($container, $firewallMap ?? []));
        $provider->setRequestStack($requestStack);
        $provider->getDecoders();

        self::assertSame($expectedFirewall, $provider->getFirewall());
    }
}
