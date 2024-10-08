<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\CacheWarmer;

use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsCertificateAuthorityProvider;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final readonly class AwsRdsCertificateAuthorityCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private AwsRdsCertificateAuthorityProvider $certificateAuthorityProvider,
    ) {
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->certificateAuthorityProvider->getCertificateAuthorityPath();

        return [];
    }
}
