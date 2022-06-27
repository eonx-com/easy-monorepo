<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class TrustedProxiesListener extends AbstractRequestEventListener
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    protected function doInvoke(RequestEvent $event): void
    {
        // Skip if parameters are not set
        if ($this->container->hasParameter('kernel.trusted_proxies') === false
            || $this->container->hasParameter('kernel.trusted_headers') === false) {
            return;
        }

        $request = $event->getRequest();
        $trustedProxies = $this->container->getParameter('kernel.trusted_proxies');
        $trustedProxies = \array_map(static function (string $trustedProxy) use ($request): string {
            $trustedProxy = \trim($trustedProxy);

            // Replace REMOTE_ADDR using request object instead of globals
            return $trustedProxy === 'REMOTE_ADDR'
                ? (string)$request->server->get('REMOTE_ADDR', '')
                : $trustedProxy;
        }, \is_array($trustedProxies) ? $trustedProxies : \explode(',', $trustedProxies));

        Request::setTrustedProxies($trustedProxies, $this->container->getParameter('kernel.trusted_headers'));
    }
}
