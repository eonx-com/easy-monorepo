<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\Listeners;

use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\Resolvers\FromHttpFoundationRequestResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class PaginationFromRequestListener
{
    /**
     * @var \EonX\EasyPagination\Interfaces\PaginationProviderInterface
     */
    private $paginationProvider;

    public function __construct(PaginationProviderInterface $paginationProvider)
    {
        $this->paginationProvider = $paginationProvider;
    }

    public function __invoke(RequestEvent $event): void
    {
        $resolver = new FromHttpFoundationRequestResolver(
            $this->paginationProvider->getPaginationConfig(),
            $event->getRequest(),
        );

        $this->paginationProvider->setResolver($resolver);
    }
}
