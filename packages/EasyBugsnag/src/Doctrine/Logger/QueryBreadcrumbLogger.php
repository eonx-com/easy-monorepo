<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Logger;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use EonX\EasyBugsnag\Doctrine\ValueObject\QueryBreadcrumb;

final readonly class QueryBreadcrumbLogger
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function log(QueryBreadcrumb $queryBreadcrumb): void
    {
        $name = 'SQL query | ' . $queryBreadcrumb->getConnectionName();

        $metadata = [
            'Params' => \json_encode($queryBreadcrumb->getQueryParameters()),
            'SQL' => $queryBreadcrumb->getQuerySql(),
            'Time (ms)' => \number_format($queryBreadcrumb->getQueryDuration() * 1000, 2),
            'Types' => \json_encode($queryBreadcrumb->getQueryTypes()),
            'Values' => \json_encode($queryBreadcrumb->getQueryValues()),
        ];

        $this->client->leaveBreadcrumb($name, Breadcrumb::PROCESS_TYPE, $metadata);
    }
}
