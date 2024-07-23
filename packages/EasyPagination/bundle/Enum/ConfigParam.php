<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bundle\Enum;

enum ConfigParam: string
{
    case PageAttribute = 'easy_pagination.page_attribute';

    case PageDefault = 'easy_pagination.page_default';

    case PerPageAttribute = 'easy_pagination.per_page_attribute';

    case PerPageDefault = 'easy_pagination.per_page_default';
}
