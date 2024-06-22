<?php
declare(strict_types=1);

namespace EonX\EasySchedule\DataCollector;

use EonX\EasySchedule\Schedule\ScheduleInterface;
use EonX\EasySchedule\Schedule\TraceableScheduleInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

final class ScheduleDataCollector extends DataCollector
{
    public const NAME = 'schedule.schedule_collector';

    public function __construct(
        private ScheduleInterface $schedule,
    ) {
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        if (($this->schedule instanceof TraceableScheduleInterface) === false) {
            return;
        }

        $this->data['providers'] = [];
        $this->data['entries'] = [];

        foreach ($this->schedule->getProviders() as $provider) {
            $class = $provider::class;

            $this->data['providers'][$class] = [
                'class' => $class,
                'entries_count' => 0,
                'file' => (new ReflectionClass($class))->getFileName(),
            ];
        }

        foreach ($this->schedule->getEntries() as $provider => $entries) {
            $this->data['providers'][$provider]['entries_count'] = \count($entries);

            foreach ($entries as $entry) {
                $this->data['entries'][] = [
                    'allowsOverlapping' => $entry->allowsOverlapping(),
                    'cronExpression' => $entry->getCronExpression(),
                    'description' => $entry->getDescription(),
                    'lockResource' => $entry->getLockResource(),
                    'maxLockTime' => $entry->getMaxLockTime(),
                    'provider' => $this->data['providers'][$provider],
                    'timezone' => $entry->getTimezone(),
                ];
            }
        }
    }

    public function getEntries(): array
    {
        return $this->data['entries'] ?? [];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string[]
     */
    public function getProviders(): array
    {
        return $this->data['providers'] ?? [];
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
