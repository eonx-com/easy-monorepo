<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class TrustedProxiesListener extends AbstractRequestListener
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    protected function doInvoke(RequestEvent $event): void
    {
        // Skip if parameters are not set
        if ($this->container->hasParameter('kernel.trusted_proxies') === false
            || $this->container->hasParameter('kernel.trusted_headers') === false) {
            return;
        }

        $request = $event->getRequest();
        /** @var string|string[] $trustedProxies */
        $trustedProxies = $this->container->getParameter('kernel.trusted_proxies');
        $trustedProxies = \array_map(static function (string $trustedProxy) use ($request): string {
            $trustedProxy = \trim($trustedProxy);

            // Replace REMOTE_ADDR using request object instead of globals
            return $trustedProxy === 'REMOTE_ADDR'
                ? (string)$request->server->get('REMOTE_ADDR', '')
                : $trustedProxy;
        }, \is_array($trustedProxies) ? $trustedProxies : \explode(',', (string)$trustedProxies));

        /** @var int-mask-of<\Symfony\Component\HttpFoundation\Request::HEADER_*> $trustedHeaders */
        $trustedHeaders = $this->container->getParameter('kernel.trusted_headers');

        Request::setTrustedProxies($trustedProxies, $trustedHeaders);
    }
}
