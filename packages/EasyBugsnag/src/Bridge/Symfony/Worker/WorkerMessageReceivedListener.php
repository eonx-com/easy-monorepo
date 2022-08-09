<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Worker;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use Carbon\Carbon;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

final class WorkerMessageReceivedListener
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    /**
     * @var \Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent
     */
    private $event;

    /**
     * @var bool
     */
    private $isSetup = false;

    /**
     * @var \DateTimeInterface
     */
    private $receivedAt;

    /**
     * @var \Symfony\Component\VarDumper\Cloner\VarCloner|null
     */
    private $varCloner;

    /**
     * @var \Symfony\Component\VarDumper\Dumper\CliDumper|null
     */
    private $varDumper;

    public function __construct(Client $client)
    {
        $this->client = $client;
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
                    'Receiver Name' => $this->event->getReceiverName(),
                    'Received At' => $this->receivedAt->format('Y-m-d H:i:s.u'),
                    'Stamps' => $this->dump($envelope->all()),
                ],
            ]);
        };

        $this->client->getPipeline()
            ->pipe(new CallbackBridge($func));
        $this->isSetup = true;
    }

    /**
     * @param mixed $var
     *
     * @throws \ErrorException
     */
    private function dump($var): string
    {
        return (string)$this->getDumper()
            ->dump($this->getCloner()->cloneVar($var), true);
    }

    private function getCloner(): VarCloner
    {
        return $this->varCloner = $this->varCloner ?? new VarCloner();
    }

    private function getDumper(): CliDumper
    {
        return $this->varDumper = $this->varDumper ?? new CliDumper();
    }
}
