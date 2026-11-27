<?php
declare(strict_types=1);

namespace EonX\EasyServerless\SnapStart\Warmer;

/**
 * Warms the application up during the AWS Lambda SnapStart initialization, right before the snapshot: what it loads
 * (classes, metadata, in-memory caches) is part of the snapshot, so the restored execution environments skip it.
 *
 * Runs once per snapshot, after the application is booted. It must not leave network connections open: every restored
 * execution environment would share them. A thrown exception is logged as a warning and the next warmers still run.
 *
 * Order: by tag priority in Symfony, higher first (e.g. #[AsTaggedItem(priority: 10)]), by tag order in Laravel.
 */
interface SnapStartWarmerInterface
{
    public function warm(): void;
}
