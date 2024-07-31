<?php
declare(strict_types=1);

namespace EonX\EasySchedule\DataCollector;

use EonX\EasySchedule\Schedule\ScheduleInterface;
use EonX\EasySchedule\Schedule\TraceableScheduleInterface;
use EonX\EasyUtils\Common\DataCollector\AbstractDataCollector;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ScheduleDataCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly ScheduleInterface $schedule,
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

    /**
     * @return string[]
     */
    public function getProviders(): array
    {
        return $this->data['providers'] ?? [];
    }
}
