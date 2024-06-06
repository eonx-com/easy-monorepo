<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stub;

use Doctrine\DBAL\Connection;

final class ConnectionStub extends Connection
{
    public function isConnected(): bool
    {
        return true;
    }
}
