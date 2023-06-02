<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Doctrine;

use Bugsnag\Client;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyBugsnag\Bridge\Doctrine\DBAL\SqlLogger;
use LaravelDoctrine\ORM\Loggers\Logger;

final class SqlOrmLogger implements Logger
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function register(EntityManagerInterface $em, Configuration $configuration): void
    {
        $configuration->setSQLLogger(new SqlLogger(
            $this->client,
            $em->getConnection(),
            null,
            $configuration->getSQLLogger(),
        ));
    }
}
