<?php
declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

interface CoverageResolverInterface
{
    /**
     * Resolve coverage for given output.
     *
     * @param string $coverageOutput
     *
     * @return float
     *
     * @throws \EonX\EasyTest\Exceptions\UnableToResolveCoverageException
     */
    public function resolve(string $coverageOutput): float;
}
