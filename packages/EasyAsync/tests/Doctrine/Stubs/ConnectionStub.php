<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Doctrine\Stubs;

use Doctrine\DBAL\Connection;

final class ConnectionStub extends Connection
{
    public function isConnected(): bool
    {
        return true;
    }
}
