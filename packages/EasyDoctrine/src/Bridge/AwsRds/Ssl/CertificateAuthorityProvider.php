<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds\Ssl;

use Symfony\Component\Filesystem\Filesystem;

final class CertificateAuthorityProvider
{
    /**
     * @see https://docs.aws.amazon.com/AmazonRDS/latest/AuroraUserGuide/UsingWithRDS.SSL.html
     */
    private const RDS_COMBINED_CA_URL = 'https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem';

    private Filesystem $filesystem;

    public function __construct(
        private readonly string $caPath,
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
