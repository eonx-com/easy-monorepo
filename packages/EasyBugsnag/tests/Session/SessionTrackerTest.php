<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Session;

use Bugsnag\Client;
use Bugsnag\Configuration;
use EonX\EasyBugsnag\Session\SessionTracker;
use EonX\EasyBugsnag\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class SessionTrackerTest extends AbstractTestCase
{
    /**
     * @see testExclude
     */
    public static function providerTestExclude(): iterable
    {
        yield 'Track' => [
            true,
            'ping',
        ];

        yield 'Do not track' => [
            false,
            'ping',
            ['ping'],
        ];

        yield 'Do not track with regex' => [
            false,
            'ping',
            ['(ping|pong)'],
        ];
    }

    /**
     * @param string[]|null $exclude
     */
    #[DataProvider('providerTestExclude')]
    public function testExclude(
        bool $trackSession,
        string $uri,
        ?array $exclude = null,
        ?string $excludeDelimiter = null,
    ): void {
        $bugsnag = new Client(new Configuration('my-api-key'));
        $request = new Request([], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
            'REQUEST_URI' => $uri,
        ]);
        $sessionTracker = new SessionTracker($bugsnag, $exclude, $excludeDelimiter);

        $sessionTracker->startSession($request);
        $currentSession = $bugsnag->getSessionTracker()
            ->getCurrentSession();

        $trackSession ? self::assertNotEmpty($currentSession) : self::assertEmpty($currentSession);
    }
}
