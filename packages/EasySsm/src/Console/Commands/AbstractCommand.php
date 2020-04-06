<?php

declare(strict_types=1);

namespace EonX\EasySsm\Console\Commands;

use EonX\EasySsm\Helpers\ConsoleRenderer;
use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\CredentialsProviderInterface;
use EonX\EasySsm\Services\Aws\SsmClientInterface;
use EonX\EasySsm\Services\Filesystem\HashDumperInterface;
use EonX\EasySsm\Services\Filesystem\SsmParametersDumperInterface;
use EonX\EasySsm\Services\Filesystem\SsmParametersParserInterface;
use EonX\EasySsm\Services\Hash\HashCheckerInterface;
use EonX\EasySsm\Services\Parameters\DiffResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractCommand extends Command
{
    /**
     * @var \EonX\EasySsm\Helpers\ConsoleRenderer
     */
    protected $consoleRenderer;

    /**
     * @var \EonX\EasySsm\Services\Parameters\DiffResolverInterface
     */
    protected $diffResolver;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \EonX\EasySsm\Services\Hash\HashCheckerInterface
     */
    protected $hashChecker;

    /**
     * @var \EonX\EasySsm\Services\Filesystem\HashDumperInterface
     */
    protected $hashDumper;

    /**
     * @var \EonX\EasySsm\Helpers\Parameters
     */
    protected $parametersHelper;

    /**
     * @var \EonX\EasySsm\Services\Aws\SsmClientInterface
     */
    protected $ssm;

    /**
     * @var \EonX\EasySsm\Services\Filesystem\SsmParametersParserInterface
     */
    protected $ssmParametersParser;

    /**
     * @var \EonX\EasySsm\Services\Filesystem\SsmParametersDumperInterface
     */
    protected $ssmParamsDumper;

    /**
     * @var \EonX\EasySsm\Services\Aws\CredentialsProviderInterface
     */
    private $awsCredentials;

    public function __construct(
        CredentialsProviderInterface $awsCredentials,
        Filesystem $filesystem,
        HashCheckerInterface $hashChecker,
        HashDumperInterface $hashDumper,
        SsmClientInterface $ssm,
        SsmParametersDumperInterface $ssmParamsDumper,
        SsmParametersParserInterface $ssmParametersParser,
        ConsoleRenderer $consoleRenderer,
        DiffResolverInterface $diffResolver,
        Parameters $parametersHelper
    ) {
        $this->awsCredentials = $awsCredentials;
        $this->filesystem = $filesystem;
        $this->hashChecker = $hashChecker;
        $this->hashDumper = $hashDumper;
        $this->ssm = $ssm;
        $this->ssmParamsDumper = $ssmParamsDumper;
        $this->ssmParametersParser = $ssmParametersParser;
        $this->consoleRenderer = $consoleRenderer;
        $this->diffResolver = $diffResolver;
        $this->parametersHelper = $parametersHelper;

        parent::__construct(null);
    }

    protected function getAwsProfile(): string
    {
        return $this->awsCredentials->getProfile() ?? 'default';
    }

    protected function getFilename(): string
    {
        return \sprintf('%s/%s.yaml', \getcwd(), $this->getAwsProfile());
    }

    protected function getOldFilename(): string
    {
        return \sprintf('%s_old', $this->getFilename());
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    protected function getRemoteParameters(SymfonyStyle $style): array
    {
        try {
            return $this->ssm->getAllParameters();
        } catch (\Throwable $exception) {
            $style->error($exception->getMessage());
        }

        return [];
    }
}
