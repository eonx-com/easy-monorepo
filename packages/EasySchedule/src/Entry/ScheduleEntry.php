<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Entry;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

final class ScheduleEntry extends AbstractScheduleEntry
{
    private bool $allowOverlapping = false;

    /**
     * @var callable[]
     */
    private array $before = [];

    private InputInterface $input;

    private float $maxLockTime = 60.0;

    private array $params;

    /**
     * @var callable[]
     */
    private array $then = [];

    public function __construct(
        private string $command,
        ?array $params = null,
    ) {
        $this->params = $params ?? [];
        $this->input = $this->buildInput();
    }

    public function allowsOverlapping(): bool
    {
        return $this->allowOverlapping;
    }

    public function before(callable $func): ScheduleEntryInterface
    {
        $this->before[] = $func;

        return $this;
    }

    public function getDescription(): string
    {
        return (string)$this->input;
    }

    public function getLockResource(): string
    {
        return \sprintf('sf-schedule-%s', \sha1($this->getExpression() . $this->command));
    }

    public function getMaxLockTime(): float
    {
        return $this->maxLockTime;
    }

    public function isDue(): bool
    {
        return $this->expressionPasses();
    }

    public function run(Application $app): void
    {
        $this->runCallbacks($app, $this->before);

        $app->run($this->input);

        $this->runCallbacks($app, $this->then);
    }

    public function setAllowOverlapping(?bool $allowOverlapping = null): ScheduleEntryInterface
    {
        $this->allowOverlapping = $allowOverlapping ?? true;

        return $this;
    }

    public function setMaxLockTime(float $seconds): ScheduleEntryInterface
    {
        $this->maxLockTime = $seconds;

        return $this;
    }

    public function then(callable $func): ScheduleEntryInterface
    {
        $this->then[] = $func;

        return $this;
    }

    private function buildInput(): InputInterface
    {
        $inputParams = [
            'command' => $this->command,
        ];

        foreach ($this->params as $key => $value) {
            $inputParams[$key] = $value;
        }

        return new ArrayInput($inputParams);
    }

    private function renderThrowable(Application $app, Throwable $throwable): void
    {
        $app->renderThrowable($throwable, new ConsoleOutput());
    }

    /**
     * @param callable[] $callbacks
     */
    private function runCallbacks(Application $app, array $callbacks): void
    {
        try {
            foreach ($callbacks as $callback) {
                \call_user_func($callback);
            }
        } catch (Throwable $exception) {
            $this->renderThrowable($app, $exception);
        }
    }
}
