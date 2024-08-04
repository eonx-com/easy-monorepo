<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Laravel\Listeners;

use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;
use Illuminate\Routing\Events\RouteMatched;

final readonly class PaginationFromRequestListener
{
    public function __construct(
        private PaginationProviderInterface $paginationProvider,
    ) {
    }

    public function handle(RouteMatched $event): void
    {
        $resolver = new FromHttpFoundationRequestPaginationResolver(
            $this->paginationProvider->getPaginationConfig(),
            $event->request
        );

        $this->paginationProvider->setResolver($resolver);
    }
}
