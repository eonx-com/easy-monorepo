<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\SsmClientInterface;
use EonX\EasySsm\Services\Aws\SsmPathResolverInterface;

final class SsmDotenv implements SsmDotenvInterface
{
    /**
     * @var \EonX\EasySsm\Services\Dotenv\EnvLoaderInterface
     */
    private $envLoader;

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

    public function __construct(
        SsmClientInterface $ssm,
        SsmPathResolverInterface $ssmPathResolver,
        Parameters $parametersHelper,
        EnvLoaderInterface $envLoader
    ) {
        $this->ssm = $ssm;
        $this->ssmPathResolver = $ssmPathResolver;
        $this->parametersHelper = $parametersHelper;
        $this->envLoader = $envLoader;
    }

    public function loadEnv(?string $path = null): void
    {
        $path = $this->ssmPathResolver->resolvePath($path);

        $this->envLoader->loadEnv(
            $this->parametersHelper->convertToEnvs(
                $this->parametersHelper->removePathFromName(
                    $this->ssm->getAllParameters($path),
                    $path
                )
            )
        );
    }
}
