<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds\Ssl;

use Symfony\Component\Filesystem\Filesystem;

final class CertificateAuthorityProvider
{
    private const RDS_COMBINED_CA_URL = 'https://s3.amazonaws.com/rds-downloads/rds-combined-ca-bundle.pem';

    private Filesystem $filesystem;

    public function __construct(
        private readonly string $caPath
    ) {
        $this->filesystem = new Filesystem();
    }

    public function getCertificateAuthorityPath(): string
    {
        if ($this->filesystem->exists($this->caPath) === false) {
            $caContents = \file_get_contents(self::RDS_COMBINED_CA_URL);

            if (\is_string($caContents) === false || $caContents === '') {
                throw new CouldNotDownloadRdsCombinedCaException('Could not download RDS Combined CA');
            }

            $this->filesystem->dumpFile($this->caPath, $caContents);
        }

        return $this->caPath;
    }
}
