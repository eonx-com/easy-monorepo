<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_PAGE_ATTRIBUTE = 'easy_pagination.page_attribute';

    /**
     * @var string
     */
    public const PARAM_PAGE_DEFAULT = 'easy_pagination.page_default';

    /**
     * @var string
     */
    public const PARAM_PER_PAGE_ATTRIBUTE = 'easy_pagination.per_page_attribute';

    /**
     * @var string
     */
    public const PARAM_PER_PAGE_DEFAULT = 'easy_pagination.per_page_default';
}
