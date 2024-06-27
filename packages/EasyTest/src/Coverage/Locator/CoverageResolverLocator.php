<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Locator;

use EonX\EasyTest\Coverage\Resolver\CoverageResolverInterface;
use InvalidArgumentException;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class CoverageResolverLocator implements CoverageResolverLocatorInterface
{
    /**
     * @param \Symfony\Contracts\Service\ServiceProviderInterface<\EonX\EasyTest\Coverage\Resolver\CoverageResolverInterface> $coverageResolvers
     */
    public function __construct(
        private ServiceProviderInterface $coverageResolvers,
    ) {
    }

    public function getCoverageResolver(string $filePath): CoverageResolverInterface
    {
        $extension = \pathinfo($filePath, \PATHINFO_EXTENSION);

        if ($this->coverageResolvers->has($extension) === false) {
            throw new InvalidArgumentException(\sprintf('Unsupported file extension: `%s`', $extension));
        }

        return $this->coverageResolvers->get($extension);
    }
}
