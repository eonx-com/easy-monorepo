<?php
declare(strict_types=1);

namespace EonX\EasyDocker\Factories;

use Symfony\Component\Finder\Finder;

final class FinderFactory
{
    /**
     * Create finder.
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public function create(): Finder
    {
        return new Finder();
    }
}
