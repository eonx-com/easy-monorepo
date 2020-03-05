<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use Throwable;

interface ExternalLogClientInterface
{
    /**
     * Clear all recorded breadcrumbs.
     *
     */
    public function clearBreadcrumbs(): void;

    /**
     * Record the given breadcrumb.
     *
     * @param string $name The name of the breadcrumb
     * @param null|string $type The type of breadcrumb
     * @param null|mixed[] $metaData Additional information about the breadcrumb
     *
     */
    public function leaveBreadcrumb(string $name, ?string $type = null, ?array $metaData = null): void;

    /**
     * Notify external service of a non-fatal/handled error.
     *
     * @param string $name The name of the error, a short (1 word) string
     * @param string $message The error message
     * @param null|callable $callback The customization callback
     *
     */
    public function notifyError(string $name, string $message, ?callable $callback = null): void;

    /**
     * Notify external service of a non-fatal/handled throwable.
     *
     * @param \Throwable $throwable The throwable to notify about.
     * @param null|callable $callback The customization callback.
     *
     */
    public function notifyException(Throwable $throwable, ?callable $callback = null): void;
}
