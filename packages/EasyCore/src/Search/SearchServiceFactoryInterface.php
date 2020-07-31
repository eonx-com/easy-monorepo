<?php

declare(strict_types=1);

namespace EonX\EasyCore\Search;

interface SearchServiceFactoryInterface
{
    public function create(): SearchServiceInterface;
}
