<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Session;

use Bugsnag\Client;
use Nette\Utils\Strings;
use Symfony\Component\HttpFoundation\Request;

final class SessionTracker
{
    /**
     * @var null|string[]
     */
    private $exclude;

    /**
     * @var string
     */
    private $excludeDelimiter;

    /**
     * @var \Bugsnag\SessionTracker
     */
    private $sessionTracker;

    /**
     * @param null|string[] $exclude
     */
    public function __construct(Client $client, ?array $exclude = null, ?string $excludeDelimiter = null)
    {
        $this->sessionTracker = $client->getSessionTracker();
        $this->exclude = $exclude ?? [];
        $this->excludeDelimiter = $excludeDelimiter ?? '#';
    }

    public function sendSessions(): void
    {
        $this->sessionTracker->sendSessions();
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
