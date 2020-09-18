<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;
use EonX\EasyLogging\Interfaces\SqlLoggerInterface;

/**
 * @deprecated since 2.4, will be removed in 3.0. Bugsnag implementation will be reworked.
 */
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

    public function __construct(ExternalLogClientInterface $client, ?bool $includeBindings = null)
    {
        $this->client = $client;
        $this->includeBindings = $includeBindings;
    }

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
