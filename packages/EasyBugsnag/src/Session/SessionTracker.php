<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Session;

use Bugsnag\Client;
use Bugsnag\SessionTracker as BugsnagSessionTracker;
use Nette\Utils\Strings;
use Symfony\Component\HttpFoundation\Request;

final class SessionTracker
{
    /**
     * @var string[]
     */
    private array $exclude;

    private string $excludeDelimiter;

    private BugsnagSessionTracker $sessionTracker;

    /**
     * @param null|string[] $exclude
     */
    public function __construct(Client $client, ?array $exclude = null, ?string $excludeDelimiter = null)
    {
        $this->sessionTracker = $client->getSessionTracker();
        $this->exclude = $exclude ?? [];
        $this->excludeDelimiter = $excludeDelimiter ?? '#';
    }

    public function startSession(Request $request): void
    {
        $requestUri = $request->getUri();

        foreach ($this->exclude as $exclude) {
            $pattern = \sprintf('%s%s%s', $this->excludeDelimiter, $exclude, $this->excludeDelimiter);

            if (Strings::match($requestUri, $pattern) !== null) {
                return;
            }
        }

        $this->sessionTracker->startSession();
    }
}
