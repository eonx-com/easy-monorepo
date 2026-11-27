<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

/**
 * @method ?int getPerPageMax() The largest page size a client can request, or null for no limit. Not declared yet to
 *                              keep existing implementations working; it will be declared in 7.0
 */
interface PaginationConfigProviderInterface
{
    public function getPageAttribute(): string;

    public function getPageDefault(): int;

    public function getPerPageAttribute(): string;

    public function getPerPageDefault(): int;
}
