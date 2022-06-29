<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony\Providers;

use EonX\EasyApiToken\Bridge\Symfony\Providers\AbstractFirewallAwareDecoderProvider;
use EonX\EasyApiToken\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class AbstractFirewallAwareDecoderProviderTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestDecoderProvider(): iterable
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
     *
     * @dataProvider providerTestDecoderProvider
     */
    public function testDecoderProvider(
        ?string $expectedFirewall,
        ?array $configs = null,
        ?array $firewallMap = null,
        ?Request $request = null
    ): void {
        $kernel = $this->getKernel($configs);
        $container = $kernel->getContainer();

        $requestStack = new RequestStack();
        if ($request !== null) {
            $requestStack->push($request);
        }

        $provider = $this->getProvider();
        $provider->setFirewallMap(new FirewallMap($container, $firewallMap ?? []));
        $provider->setRequestStack($requestStack);
        $provider->getDecoders();

        self::assertEquals($expectedFirewall, $provider->getFirewall());
    }

    private function getProvider(): AbstractFirewallAwareDecoderProvider
    {
        return new class() extends AbstractFirewallAwareDecoderProvider {
            private ?string $firewallName = null;

            public function getFirewall(): ?string
            {
                return $this->firewallName;
            }

            protected function doGetDecoders(?string $firewall = null): iterable
            {
                $this->firewallName = $firewall;

                return [];
            }

            protected function doGetDefaultDecoder(?string $firewall = null): ?string
            {
                $this->firewallName = $firewall;

                return null;
            }

            public function getPriority(): int
            {
                return 0;
            }
        };
    }
}
