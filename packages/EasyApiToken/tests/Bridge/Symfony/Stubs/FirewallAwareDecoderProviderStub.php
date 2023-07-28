<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiToken\Bridge\Symfony\Providers\AbstractFirewallAwareDecoderProvider;

final class FirewallAwareDecoderProviderStub extends AbstractFirewallAwareDecoderProvider
{
    private ?string $firewallName = null;

    public function getFirewall(): ?string
    {
        return $this->firewallName;
    }

    public function getPriority(): int
    {
        return 0;
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
}
