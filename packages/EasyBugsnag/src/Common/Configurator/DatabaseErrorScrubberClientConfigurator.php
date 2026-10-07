<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use Closure;

final class DatabaseErrorScrubberClientConfigurator extends AbstractClientConfigurator
{
    /**
     * Database drivers embed verbatim row/column values in constraint-violation messages, which then reach
     * Bugsnag through the exception message. Each pattern strips or masks one such driver-specific leak so the
     * scrubber is not tied to a single engine.
     */
    private const array SCRUB_PATTERNS = [
        // MySQL / MariaDB inline the conflicting value on unique-key violations, e.g.
        // "1062 Duplicate entry 'john@example.com' for key 'users.email_unique'"
        "/Duplicate entry '.+?' for key '/" => "Duplicate entry '*REDACTED*' for key '",
        // PostgreSQL appends a "DETAIL:" line carrying the offending row, e.g.
        // "DETAIL:  Failing row contains (...)" or "DETAIL:  Key (email)=(john@example.com) already exists."
        '/(\r\n|\r|\n)?[ \t]*DETAIL:[^\r\n]*/' => '',
    ];

    public function configure(Client $bugsnag): void
    {
        $bugsnag->registerCallback(static function (Report $report): void {
            $scrub = static function (?string $message): ?string {
                if (\is_string($message) === false) {
                    return $message;
                }

                return (string)\preg_replace(
                    \array_keys(self::SCRUB_PATTERNS),
                    \array_values(self::SCRUB_PATTERNS),
                    $message
                );
            };

            $report->setMessage($scrub($report->getMessage()));

            $previousScrubber = Closure::bind(
                function () use ($scrub): void {
                    $previous = $this->previous ?? null;

                    while ($previous instanceof Report) {
                        $previous->setMessage($scrub($previous->getMessage()));
                        $previous = $previous->previous ?? null;
                    }
                },
                $report,
                Report::class
            );

            $previousScrubber();
        });
    }
}
