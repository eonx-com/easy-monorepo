<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Provider;

use EonX\EasyDoctrine\AwsRds\Exception\CouldNotDownloadRdsCombinedCaException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class AwsRdsCertificateAuthorityProvider
{
    /**
     * @see https://docs.aws.amazon.com/AmazonRDS/latest/AuroraUserGuide/UsingWithRDS.SSL.html
     */
    private const RDS_COMBINED_CA_URL = 'https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem';

    private Filesystem $filesystem;

    public function __construct(
        private readonly string $caPath,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function getCertificateAuthorityPath(): string
    {
        $this->logger?->debug('Resolving AWS RDS CA path');

        if ($this->filesystem->exists($this->caPath) === false) {
            $this->logger?->debug('CA file does not exist');

            $caContents = \file_get_contents(self::RDS_COMBINED_CA_URL);

            $this->logger?->debug('Downloaded RDS CA from public URL');

            if (\is_string($caContents) === false || $caContents === '') {
                throw new CouldNotDownloadRdsCombinedCaException('Could not download RDS Combined CA');
            }

            $this->filesystem->dumpFile($this->caPath, $caContents);

            $this->logger?->debug('Created CA file');
        }

        return $this->caPath;
    }
}
