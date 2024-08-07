<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Stub\Statement;

use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use EonX\EasyBugsnag\Tests\Stub\Result\ResultStub;

final class StatementStub implements Statement
{
    public function bindParam(mixed $param, mixed &$variable, mixed $type = null, mixed $length = null): bool
    {
        return false;
    }

    public function bindValue(mixed $param, mixed $value, mixed $type = null): bool
    {
        return false;
    }

    public function execute(mixed $params = null): Result
    {
        return new ResultStub();
    }
}
