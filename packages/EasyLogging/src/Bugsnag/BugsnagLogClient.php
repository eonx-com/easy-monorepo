<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bugsnag;

use Bugsnag\Client as BugsnagClient;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;
use Throwable;

final class BugsnagLogClient implements ExternalLogClientInterface
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    public function __construct(BugsnagClient $client)
    {
        $this->client = $client;
    }

    public function clearBreadcrumbs(): void
    {
        $this->client->clearBreadcrumbs();
    }

    /**
     * @param null|mixed[] $metaData Additional information about the breadcrumb
     */
    public function leaveBreadcrumb(string $name, ?string $type = null, ?array $metaData = null): void
    {
        $this->client->leaveBreadcrumb($name, $type, $metaData ?? []);
    }

    public function notifyError(string $name, string $message, ?callable $callback = null): void
    {
        $this->client->notifyError($name, $message, $callback);
    }

    public function notifyException(Throwable $throwable, ?callable $callback = null): void
    {
        $this->client->notifyException($throwable, $callback);
    }
}
