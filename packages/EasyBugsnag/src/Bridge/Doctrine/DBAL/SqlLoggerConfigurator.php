<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Doctrine\DBAL;

use Bugsnag\Client;
use Doctrine\ORM\EntityManagerInterface;

final class SqlLoggerConfigurator
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function configure(EntityManagerInterface $entityManager): void
    {
        $config = $entityManager->getConfiguration();
        $config->setSQLLogger(new SqlLogger($this->client, $entityManager->getConnection(), $config->getSQLLogger()));
    }
}
