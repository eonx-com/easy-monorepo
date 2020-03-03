<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;
use EonX\EasyLogging\Interfaces\SqlLoggerInterface;

final class ExternalLogger implements SqlLoggerInterface
{
    /**
     * @var \EonX\EasyLogging\Interfaces\ExternalLogClientInterface
     */
    private $client;

    /**
     * @var null|bool
     */
    private $includeBindings;

    /**
     * BugsnagLogger constructor.
     *
     * @param \EonX\EasyLogging\Interfaces\ExternalLogClientInterface $client
     * @param null|bool $includeBindings
     */
    public function __construct(ExternalLogClientInterface $client, ?bool $includeBindings = null)
    {
        $this->client = $client;
        $this->includeBindings = $includeBindings;
    }

    /**
     * Register logger.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Doctrine\ORM\Configuration $configuration
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function register(EntityManagerInterface $em, Configuration $configuration): void
    {
        $logger = new ExternalSqlLogger(
            $this->client,
            $em->getConnection()->getDatabasePlatform()->getName(),
            $this->includeBindings
        );

        $configuration->setSQLLogger($logger);
    }
}
