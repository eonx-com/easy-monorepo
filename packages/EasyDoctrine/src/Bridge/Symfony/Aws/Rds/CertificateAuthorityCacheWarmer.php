<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use EonX\EasyDoctrine\Bridge\AwsRds\Ssl\CertificateAuthorityProvider;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final readonly class CertificateAuthorityCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private CertificateAuthorityProvider $certificateAuthorityProvider,
    ) {
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir): array
    {
        $this->certificateAuthorityProvider->getCertificateAuthorityPath();

        return [];
    }
}
