<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\CacheWarmer;

use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsCertificateAuthorityProvider;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class AwsRdsCertificateAuthorityCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly AwsRdsCertificateAuthorityProvider $certificateAuthorityProvider,
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
