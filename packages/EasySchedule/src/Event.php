<?php
declare(strict_types=1);

namespace EonX\EasySchedule;

use EonX\EasySchedule\Interfaces\EventInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

final class Event extends AbstractEvent
{
    /**
     * @var bool
     */
    private $allowOverlapping = false;

    /**
     * @var callable[]
     */
    private $before = [];

    /**
     * @var string
     */
    private $command;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var float
     */
    private $maxLockTime = 60.0;

    /**
     * @var mixed[]
     */
    private $params;

    /**
     * @var callable[]
     */
    private $then = [];

    /**
     * @param null|mixed[] $params
     */
    public function __construct(string $command, ?array $params = null)
    {
        $this->command = $command;
        $this->params = $params ?? [];
        $this->input = $this->buildInput();
    }

    public function allowsOverlapping(): bool
    {
        return $this->allowOverlapping;
    }

    public function before(callable $func): EventInterface
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

    public function setAllowOverlapping(?bool $allowOverlapping = null): EventInterface
    {
        $this->allowOverlapping = $allowOverlapping ?? true;

        return $this;
    }

    public function setMaxLockTime(float $seconds): EventInterface
    {
        $this->maxLockTime = $seconds;

        return $this;
    }

    public function then(callable $func): EventInterface
    {
        $this->then[] = $func;

        return $this;
    }

    private function buildInput(): InputInterface
    {
        $inputParams = ['command' => $this->command];

        foreach ($this->params as $key => $value) {
            $inputParams[$key] = $value;
        }

        return new ArrayInput($inputParams);
    }

    private function renderThrowable(Application $app, \Throwable $throwable): void
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
        } catch (\Throwable $exception) {
            $this->renderThrowable($app, $exception);
        }
    }
}
