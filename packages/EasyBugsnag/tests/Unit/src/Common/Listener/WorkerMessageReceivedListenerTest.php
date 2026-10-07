<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Listener;

use Bugsnag\Client;
use EonX\EasyBugsnag\Common\Listener\WorkerMessageReceivedListener;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class WorkerMessageReceivedListenerTest extends AbstractUnitTestCase
{
    public function testItSanitizesDumpedMessageMetadata(): void
    {
        $client = self::getService(Client::class);
        $sanitizer = self::getService(SensitiveDataSanitizerInterface::class);

        $listener = new WorkerMessageReceivedListener($client, $sanitizer);

        $message = new stdClass();
        // Luhn-valid test card — the sanitizer detects it by value, not by key
        $message->card = '4111111111111111';
        $event = new WorkerMessageReceivedEvent(new Envelope($message), 'async');

        $listener($event);

        $client->notifyError('test', 'test');

        /** @var \Bugsnag\HttpClient $httpClient */
        $httpClient = self::getPrivatePropertyValue($client, 'http');
        /** @var \Bugsnag\Report[] $reports */
        $reports = self::getPrivatePropertyValue($httpClient, 'queue');
        $worker = $reports[0]->getMetaData()['worker'];

        self::assertStringNotContainsString('4111111111111111', $worker['Message']);
        self::assertSame('async', $worker['Receiver Name']);
    }
}
