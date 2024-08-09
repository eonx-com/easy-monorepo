<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Logger;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use EonX\EasyBugsnag\Doctrine\ValueObject\Query;

final readonly class BreadcrumbLogger
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function log(string $connectionName, Query $query): void
    {
        $query->stop();

        $name = 'SQL query | ' . $connectionName;

        $metadata = [
            'Params' => \json_encode($query->getParams()),
            'SQL' => $query->getSql(),
            'Time (ms)' => \number_format($query->getDuration() * 1000, 2),
            'Types' => \json_encode($query->getTypes()),
        ];

        $this->client->leaveBreadcrumb($name, Breadcrumb::PROCESS_TYPE, $metadata);
    }
}
