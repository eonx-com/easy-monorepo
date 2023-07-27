<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Locators;

use EonX\EasyTest\Interfaces\CoverageResolverInterface;
use EonX\EasyTest\Interfaces\CoverageResolverLocatorInterface;
use InvalidArgumentException;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class CoverageResolverLocator implements CoverageResolverLocatorInterface
{
    /**
     * @param \Symfony\Contracts\Service\ServiceProviderInterface<\EonX\EasyTest\Interfaces\CoverageResolverInterface> $serviceLocator
     */
    public function __construct(
        private ServiceProviderInterface $serviceLocator,
    ) {
    }

    public function getCoverageResolver(string $filePath): CoverageResolverInterface
    {
        $extension = \pathinfo($filePath, \PATHINFO_EXTENSION);

        if ($this->serviceLocator->has($extension) === false) {
            throw new InvalidArgumentException(\sprintf('Unsupported file extension: `%s`', $extension));
        }

        return $this->serviceLocator->get($extension);
    }
}
