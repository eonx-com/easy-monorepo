<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Asset\Package;

use Symfony\Component\Asset\PackageInterface;

use function Symfony\Component\String\u;

final readonly class PrefixedUrlPackage implements PackageInterface
{
    public function __construct(
        private string $assetsUrl,
        private PackageInterface $decorated,
    ) {
    }

    public function getUrl(string $path): string
    {
        $baseUrl = u($this->assetsUrl)
            ->trimEnd('/');

        $path = u($this->decorated->getUrl($path))
            ->trimStart('/');

        return \sprintf('%s/%s', $baseUrl, $path);
    }

    public function getVersion(string $path): string
    {
        return $this->decorated->getVersion($path);
    }
}
