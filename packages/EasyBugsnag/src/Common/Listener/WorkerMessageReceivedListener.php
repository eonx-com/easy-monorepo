<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Listener;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use Carbon\Carbon;
use DateTimeInterface;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Throwable;

final class WorkerMessageReceivedListener
{
    private const int MAX_EXPAND_DEPTH = 10;

    private WorkerMessageReceivedEvent $event;

    private bool $isSetup = false;

    private DateTimeInterface $receivedAt;

    private ?VarCloner $varCloner = null;

    private ?CliDumper $varDumper = null;

    public function __construct(
        private readonly Client $client,
        private readonly ?SensitiveDataSanitizerInterface $sensitiveDataSanitizer = null,
    ) {}

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

    private static function expand(mixed $value, int $depth = 0): mixed
    {
        if ($depth >= self::MAX_EXPAND_DEPTH) {
            return \is_object($value) ? \get_debug_type($value) : $value;
        }

        if (\is_array($value)) {
            return \array_map(static fn(mixed $item): mixed => self::expand($item, $depth + 1), $value);
        }

        if (\is_object($value) === false || $value instanceof DateTimeInterface || $value instanceof Throwable) {
            return $value;
        }

        $expanded = ['@class' => \get_debug_type($value)];

        foreach ((array)$value as $key => $item) {
            $expanded[\substr((string)\strrchr("\0" . $key, "\0"), 1)] = self::expand($item, $depth + 1);
        }

        return $expanded;
    }

    private function dump(mixed $var): string
    {
        // Expand objects before masking: the sanitizer's generic transformer (json_encode) keeps neither
        // non-public properties nor the class; key-based masking cannot work on the dumped string
        if ($this->sensitiveDataSanitizer !== null) {
            $var = $this->sensitiveDataSanitizer->sanitize(self::expand($var));
        }

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
