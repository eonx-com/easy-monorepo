<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

abstract class AbstractStoreTestCase extends AbstractTestCase
{
    protected ?Connection $doctrineDbal = null;

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

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getDoctrineDbalConnection(): Connection
    {
        if ($this->doctrineDbal !== null) {
            return $this->doctrineDbal;
        }

        $this->doctrineDbal = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);

        return $this->doctrineDbal;
    }
}
