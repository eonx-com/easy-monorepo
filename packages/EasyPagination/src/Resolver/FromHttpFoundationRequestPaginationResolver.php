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
        $config = $this->paginationConfigProvider;

        $perPage = $this->getPositiveInt($config->getPerPageAttribute(), $config->getPerPageDefault());

        // @todo Call it directly in 7.0, when the interface declares it
        $perPageMax = \method_exists($config, 'getPerPageMax') ? $config->getPerPageMax() : null;

        if ($perPageMax !== null && $perPage > $perPageMax) {
            $perPage = $perPageMax;
        }

        // The offset of the page, (page - 1) * perPage, and the number of the next page must both fit in an integer.
        // A configured default is used as is, so the page size can still be below 1 here
        $pageMax = \min(\intdiv(\PHP_INT_MAX, \max($perPage, 1)), \PHP_INT_MAX - 2) + 1;
        $pageDefault = \min($config->getPageDefault(), $pageMax);
        $page = $this->getPositiveInt($config->getPageAttribute(), $pageDefault, $pageMax);

        return Pagination::create(
            $page,
            $perPage,
            $config->getPageAttribute(),
            $config->getPerPageAttribute(),
            $this->request->getUri()
        );
    }

    /**
     * A value that is not an integer from 1 to the given maximum is a client error (an SQL error, a division by zero,
     * an integer overflow or no limit at all), so the default is used instead. FILTER_VALIDATE_INT, unlike (int),
     * rejects values like "30abc" or "1.5"
     */
    private function getPositiveInt(string $attribute, int $default, int $max = \PHP_INT_MAX): int
    {
        $value = \filter_var(
            $this->request->query->get($attribute, $default),
            \FILTER_VALIDATE_INT,
            ['options' => ['max_range' => $max, 'min_range' => 1]]
        );

        return \is_int($value) ? $value : $default;
    }
}
