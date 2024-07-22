<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Stub\SqlLogger;

use Doctrine\DBAL\Logging\SQLLogger;

final class SqlLoggerStub implements SQLLogger
{
    public function startQuery(mixed $sql, ?array $params = null, ?array $types = null): void
    {
        // TODO: Implement startQuery() method
    }

    public function stopQuery(): void
    {
        // TODO: Implement stopQuery() method
    }
}
