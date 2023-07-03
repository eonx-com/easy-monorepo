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

    /**
     * @return mixed[]
     */
    public function fetchAllAssociative(): array
    {
        return [];
    }

    /**
     * @return mixed[]
     */
    public function fetchAllNumeric(): array
    {
        return [];
    }

    /**
     * @return array<string,mixed>|false
     */
    public function fetchAssociative(): array|false
    {
        return [];
    }

    /**
     * @return mixed[]
     */
    public function fetchFirstColumn(): array
    {
        return [];
    }

    /**
     * @return list<mixed>|false
     */
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
