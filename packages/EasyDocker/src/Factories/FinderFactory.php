<?php

declare(strict_types=1);

namespace EonX\EasyDocker\Factories;

use Symfony\Component\Finder\Finder;

final class FinderFactory
{
    public function create(): Finder
    {
        return new Finder();
    }
}
