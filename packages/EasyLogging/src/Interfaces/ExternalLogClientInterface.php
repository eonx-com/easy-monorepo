<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use Throwable;

interface ExternalLogClientInterface
{
    public function clearBreadcrumbs(): void;

    /**
     * @param null|mixed[] $metaData Additional information about the breadcrumb
     */
    public function leaveBreadcrumb(string $name, ?string $type = null, ?array $metaData = null): void;

    public function notifyError(string $name, string $message, ?callable $callback = null): void;

    public function notifyException(Throwable $throwable, ?callable $callback = null): void;
}
