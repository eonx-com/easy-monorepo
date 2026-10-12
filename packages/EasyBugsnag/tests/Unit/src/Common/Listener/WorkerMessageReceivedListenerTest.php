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
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

final class WorkerMessageReceivedListenerTest extends AbstractUnitTestCase
{
    public function testItDumpsWithoutMaskingWhenSanitizerIsNull(): void
    {
        $client = self::getService(Client::class);
        $listener = new WorkerMessageReceivedListener($client);

        $message = new stdClass();
        $message->password = 'super-secret';
        $listener(new WorkerMessageReceivedEvent(new Envelope($message), 'async'));

        $worker = $this->getWorkerMetadata($client);
        self::assertStringContainsString('super-secret', $worker['Message']);
        self::assertSame('async', $worker['Receiver Name']);
    }

    public function testItKeepsPrivatePropertiesAndClassNameWhenMasking(): void
    {
        $client = self::getService(Client::class);
        $listener = new WorkerMessageReceivedListener(
            $client,
            self::getService(SensitiveDataSanitizerInterface::class)
        );

        $message = new readonly class ('super-secret') {
            public function __construct(
                private string $password,
            ) {}

            public function getPassword(): string
            {
                return $this->password;
            }
        };
        $listener(new WorkerMessageReceivedEvent(
            new Envelope($message, [new TransportMessageIdStamp('9001')]),
            'async'
        ));

        $worker = $this->getWorkerMetadata($client);
        self::assertStringContainsString('@class', $worker['Message']);
        self::assertStringContainsString('password', $worker['Message']);
        self::assertStringNotContainsString('super-secret', $worker['Message']);
        self::assertStringContainsString('9001', $worker['Stamps']);
    }

    public function testItMasksCardNumberByValue(): void
    {
        $client = self::getService(Client::class);
        $listener = new WorkerMessageReceivedListener(
            $client,
            self::getService(SensitiveDataSanitizerInterface::class)
        );

        $message = new stdClass();
        // Luhn-valid test card — masked by value, not by key
        $message->reference = '4111111111111111';
        $listener(new WorkerMessageReceivedEvent(new Envelope($message), 'async'));

        self::assertStringNotContainsString('4111111111111111', $this->getWorkerMetadata($client)['Message']);
    }

    public function testItMasksSensitiveKeysInDumpedMessage(): void
    {
        $client = self::getService(Client::class);
        $listener = new WorkerMessageReceivedListener(
            $client,
            self::getService(SensitiveDataSanitizerInterface::class)
        );

        $message = new stdClass();
        $message->password = 'super-secret';
        $listener(new WorkerMessageReceivedEvent(new Envelope($message), 'async'));

        self::assertStringNotContainsString('super-secret', $this->getWorkerMetadata($client)['Message']);
    }

    /**
     * @return array<string, string>
     */
    private function getWorkerMetadata(Client $client): array
    {
        $client->notifyError('test', 'test');

        /** @var \Bugsnag\HttpClient $httpClient */
        $httpClient = self::getPrivatePropertyValue($client, 'http');
        /** @var \Bugsnag\Report[] $reports */
        $reports = self::getPrivatePropertyValue($httpClient, 'queue');

        return $reports[0]->getMetaData()['worker'];
    }
}
