<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\SsmClientInterface;
use EonX\EasySsm\Services\Aws\SsmPathResolverInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class SsmDotenv implements SsmDotenvInterface
{
    /**
     * @var \EonX\EasySsm\Services\Dotenv\EnvLoaderInterface
     */
    private $envLoader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \EonX\EasySsm\Helpers\Parameters
     */
    private $parametersHelper;

    /**
     * @var \EonX\EasySsm\Services\Aws\SsmClientInterface
     */
    private $ssm;

    /**
     * @var \EonX\EasySsm\Services\Aws\SsmPathResolverInterface
     */
    private $ssmPathResolver;

    /**
     * @var bool
     */
    private $strict;

    public function __construct(
        SsmClientInterface $ssm,
        SsmPathResolverInterface $ssmPathResolver,
        Parameters $parametersHelper,
        EnvLoaderInterface $envLoader,
        ?LoggerInterface $logger = null,
        ?bool $strict = null,
    ) {
        $this->ssm = $ssm;
        $this->ssmPathResolver = $ssmPathResolver;
        $this->parametersHelper = $parametersHelper;
        $this->envLoader = $envLoader;
        $this->strict = $strict ?? false;

        $this->setLogger($logger);
    }

    public function loadEnv(?string $path = null): void
    {
        $path = $this->ssmPathResolver->resolvePath($path);

        $this->envLoader->loadEnv(
            $this->parametersHelper->convertToEnvs(
                $this->parametersHelper->removePathFromName($this->getParameters($path), $path)
            )
        );
    }

    public function setLogger(?LoggerInterface $logger = null): SsmDotenvInterface
    {
        $this->logger = $logger ?? new NullLogger();

        return $this;
    }

    public function setStrict(bool $strict): SsmDotenvInterface
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     *
     * @throws \Throwable
     */
    private function getParameters(?string $path = null): array
    {
        try {
            return $this->ssm->getAllParameters($path);
        } catch (\Throwable $throwable) {
            $this->logger->info(\sprintf(
                '[EasySsm][Dotenv] Error while fetching SSM params: %s',
                $throwable->getMessage()
            ));

            if ($this->strict === false) {
                $this->logger->info('[EasySsm][Dotenv] Strict option is false so carry on');

                return [];
            }

            throw $throwable;
        }
    }
}
