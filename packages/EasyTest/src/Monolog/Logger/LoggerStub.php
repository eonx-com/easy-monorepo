<?php
declare(strict_types=1);

namespace EonX\EasyTest\Monolog\Logger;

use BadMethodCallException;
use DateTimeZone;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;

/**
 * @method bool hasRecord($record, $level)
 * @method bool hasInfo($record)
 * @method bool hasWarning($record)
 * @method void reset()
 * @method void clear()
 * @method array getRecords()
 */
final class LoggerStub extends Logger
{
    private readonly TestHandler $testHandler;

    /**
     * @param \Monolog\Handler\HandlerInterface[]|null $handlers
     * @param \Monolog\Processor\ProcessorInterface[]|callable[]|null $processors
     */
    public function __construct(
        string $name,
        ?array $handlers = null,
        ?array $processors = null,
        ?DateTimeZone $timezone = null,
    ) {
        $this->testHandler = new TestHandler();
        $this->testHandler->setSkipReset(true);

        parent::__construct(
            name: $name,
            handlers: [$this->testHandler],
            processors: $processors ?? [],
            timezone: $timezone
        );
    }

    /**
     * @param string[] $args
     */
    public function __call(string $method, array $args): mixed
    {
        $pattern = '/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/';
        if (\preg_match($pattern, $method, $matches) > 0) {
            $genericMethod = $matches[1] . ($matches[3] !== 'Records' ? 'Record' : '') . $matches[3];
            if (\method_exists($this->testHandler, $genericMethod)) {
                $args[] = Level::fromName((string)$matches[2]);

                return $this->testHandler->$genericMethod(...$args);
            }
        }

        if (\method_exists($this->testHandler, $method)) {
            return $this->testHandler->$method(...$args);
        }

        throw new BadMethodCallException('Call to undefined method ' . self::class . '::' . $method . '()');
    }
}
