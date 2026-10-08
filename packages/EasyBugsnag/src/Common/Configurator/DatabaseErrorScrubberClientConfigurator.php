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
     * scrubber is not tied to a single engine. The list covers the known value-bearing errors, not every
     * possible one — a message denylist is inherently incomplete, so add new drivers/formats as they surface.
     */
    private const array SCRUB_PATTERNS = [
        // --- MySQL / MariaDB ---
        // 1062: Duplicate entry 'john@example.com' for key 'users.email_unique'
        "/Duplicate entry '.+?' for key '/" => "Duplicate entry '*REDACTED*' for key '",
        // 1366: Incorrect integer value: 'abc' for column 'age' at row 1 (also string/decimal/datetime/...)
        "/Incorrect (\\w+) value: '.+?' for column/" => "Incorrect \\1 value: '*REDACTED*' for column",
        // 1292: Truncated incorrect DATE value: '2021-13-45' (also DOUBLE/DECIMAL/...)
        "/Truncated incorrect (\\w+) value: '.+?'/" => "Truncated incorrect \\1 value: '*REDACTED*'",
        // Date/time field value out of range: "2021-13-45"
        '/date\/time field value out of range: ".+?"/' => 'date/time field value out of range: "*REDACTED*"',
        // Value echoed in the main message, e.g. invalid input syntax for type integer: "abc"
        // or invalid input value for enum mood: "bad"
        '/(invalid input syntax for type|invalid input value for enum) (.+?): ".+?"/' => '\1 \2: "*REDACTED*"',
        // --- PostgreSQL ---
        // "DETAIL:" line carries the offending row, e.g. "DETAIL:  Failing row contains (...)"
        // or "DETAIL:  Key (email)=(john@example.com) already exists."
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
