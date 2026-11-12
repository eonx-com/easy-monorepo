<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Resolver;

use EonX\EasyPagination\Pagination\Pagination;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Provider\PaginationConfigProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class FromHttpFoundationRequestPaginationResolver
{
    public function __construct(
        private PaginationConfigProviderInterface $paginationConfigProvider,
        private Request $request,
    ) {
        // No body needed
    }

    public function __invoke(): PaginationInterface
    {
        $query = $this->request->query;
        $config = $this->paginationConfigProvider;

        $page = $this->getPositiveInt($config->getPageAttribute(), $config->getPageDefault());
        $perPage = $this->getPositiveInt($config->getPerPageAttribute(), $config->getPerPageDefault());

        // @todo Call it directly in 7.0, when the interface declares it
        $perPageMax = \method_exists($config, 'getPerPageMax') ? $config->getPerPageMax() : null;

        if ($perPageMax !== null && $perPage > $perPageMax) {
            $perPage = $perPageMax;
        }

        return Pagination::create(
            $page,
            $perPage,
            $config->getPageAttribute(),
            $config->getPerPageAttribute(),
            $this->request->getUri()
        );
    }

    /**
     * A value that is not an integer of at least 1 is a client error (an SQL error, a division by zero or no limit at
     * all), so the default is used instead. FILTER_VALIDATE_INT, unlike (int), rejects values like "30abc" or "1.5"
     */
    private function getPositiveInt(string $attribute, int $default): int
    {
        $value = \filter_var(
            $this->request->query->get($attribute, $default),
            \FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        return \is_int($value) ? $value : $default;
    }
}
