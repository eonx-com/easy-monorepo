<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony\DataCollector;

use EonX\EasySchedule\Bridge\Symfony\Interfaces\TraceableScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class ScheduleDataCollector extends DataCollector
{
    /**
     * @var string
     */
    public const NAME = 'schedule.schedule_collector';

    /**
     * @var \EonX\EasySchedule\Interfaces\ScheduleInterface
     */
    private $schedule;

    public function __construct(ScheduleInterface $schedule)
    {
        $this->schedule = $schedule;
    }

    public function collect(Request $request, Response $response, ?\Throwable $throwable = null): void
    {
        if (($this->schedule instanceof TraceableScheduleInterface) === false) {
            return;
        }

        /** @var \EonX\EasySchedule\Bridge\Symfony\Interfaces\TraceableScheduleInterface $schedule */
        $schedule = $this->schedule;

        $this->data['providers'] = [];
        $this->data['events'] = [];

        foreach ($schedule->getProviders() as $provider) {
            $class = \get_class($provider);

            $this->data['providers'][$class] = [
                'class' => $class,
                'events_count' => 0,
                'file' => (new \ReflectionClass($class))->getFileName(),
            ];
        }

        foreach ($schedule->getEvents() as $provider => $events) {
            /** @var \EonX\EasySchedule\Interfaces\EventInterface[] $events */
            $this->data['providers'][$provider]['events_count'] = \count($events);

            foreach ($events as $event) {
                /** @var \EonX\EasySchedule\Interfaces\EventInterface $event */
                $this->data['events'][] = [
                    'allowsOverlapping' => $event->allowsOverlapping(),
                    'description' => $event->getDescription(),
                    'cronExpression' => $event->getCronExpression(),
                    'maxLockTime' => $event->getMaxLockTime(),
                    'lockResource' => $event->getLockResource(),
                    'provider' => $this->data['providers'][$provider],
                    'timezone' => $event->getTimezone(),
                ];
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function getEvents(): array
    {
        return $this->data['events'];
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
        return $this->data['providers'];
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
