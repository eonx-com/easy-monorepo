<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Interfaces;

interface PaginationConstants
{
    /**
     * Page attribute name to retrieve the current page number.
     *
     * @var string
     */
    public const ATTRS_PAGE = 'page';

    /**
     * Per page attribute name to retrieve how many items per page.
     *
     * @var string
     */
    public const ATTRS_PER_PAGE = 'perPage';

    /**
     * Default page number if not provider.
     *
     * @var int
     */
    public const DEFAULT_PAGE = 1;

    /**
     * Default per page number if not provider.
     *
     * @var int
     */
    public const DEFAULT_PER_PAGE = 10;
}