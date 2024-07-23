<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Listener;

use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class PaginationFromRequestListener
{
    public function __construct(
        private PaginationProviderInterface $paginationProvider,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $resolver = new FromHttpFoundationRequestPaginationResolver(
            $this->paginationProvider->getPaginationConfig(),
            $event->getRequest()
        );

        $this->paginationProvider->setResolver($resolver);
    }
}
