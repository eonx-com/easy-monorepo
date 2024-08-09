<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Listener;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use Carbon\Carbon;
use DateTimeInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

final class WorkerMessageReceivedListener
{
    private WorkerMessageReceivedEvent $event;

    private bool $isSetup = false;

    private DateTimeInterface $receivedAt;

    private ?VarCloner $varCloner = null;

    private ?CliDumper $varDumper = null;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(WorkerMessageReceivedEvent $event): void
    {
        // Set event on class to use when notifying bugsnag
        $this->event = $event;
        $this->receivedAt = Carbon::now('UTC');

        // Prevent adding the callback to pipe more than once
        if ($this->isSetup) {
            return;
        }

        $func = function (Report $report): void {
            $envelope = $this->event->getEnvelope();

            $report->addMetaData([
                'worker' => [
                    'Message' => $this->dump($envelope->getMessage()),
                    'Received At' => $this->receivedAt->format('Y-m-d H:i:s.u'),
                    'Receiver Name' => $this->event->getReceiverName(),
                    'Stamps' => $this->dump($envelope->all()),
                ],
            ]);
        };

        $this->client->getPipeline()
            ->pipe(new CallbackBridge($func));
        $this->isSetup = true;
    }

    private function dump(mixed $var): string
    {
        return (string)$this->getDumper()
            ->dump($this->getCloner()->cloneVar($var), true);
    }

    private function getCloner(): VarCloner
    {
        if ($this->varCloner !== null) {
            return $this->varCloner;
        }

        $this->varCloner = new VarCloner();

        return $this->varCloner;
    }

    private function getDumper(): CliDumper
    {
        if ($this->varDumper !== null) {
            return $this->varDumper;
        }

        $this->varDumper = new CliDumper();

        return $this->varDumper;
    }
}
