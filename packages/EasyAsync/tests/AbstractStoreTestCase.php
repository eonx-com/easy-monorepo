<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

abstract class AbstractStoreTestCase extends AbstractTestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineDbal;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getDoctrineDbalConnection(): Connection
    {
        if ($this->doctrineDbal !== null) {
            return $this->doctrineDbal;
        }

        return $this->doctrineDbal = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);
    }

    protected function setUp(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $conn->connect();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $conn->close();

        parent::tearDown();
    }
}
