<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Driver\Result;

final class ResultStub implements Result
{
    public function columnCount(): int
    {
        return 0;
    }

    public function fetchAllAssociative(): array
    {
        return [];
    }

    public function fetchAllNumeric(): array
    {
        return [];
    }

    public function fetchAssociative(): array|false
    {
        return [];
    }

    public function fetchFirstColumn(): array
    {
        return [];
    }

    public function fetchNumeric(): array|false
    {
        return [];
    }

    public function fetchOne(): void
    {
    }

    public function free(): void
    {
    }

    public function rowCount(): int
    {
        return 0;
    }
}
