<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Listeners;

use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\Resolvers\FromHttpFoundationRequestResolver;
use Illuminate\Routing\Events\RouteMatched;

final class FromRequestPaginationListener
{
    /**
     * @var \EonX\EasyPagination\Interfaces\PaginationProviderInterface
     */
    private $paginationProvider;

    public function __construct(PaginationProviderInterface $paginationProvider)
    {
        $this->paginationProvider = $paginationProvider;
    }

    public function handle(RouteMatched $event): void
    {
        $resolver = new FromHttpFoundationRequestResolver(
            $this->paginationProvider->getPaginationConfig(),
            $event->request
        );

        $this->paginationProvider->setResolver($resolver);
    }
}
