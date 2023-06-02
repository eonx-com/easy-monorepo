<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class StreamHandlerConfigProvider implements HandlerConfigProviderInterface
{
    /**
     * @var null|string[]
     */
    private $channels;

    /**
     * @var null|string[]
     */
    private $exceptChannels;

    /**
     * @var null|int
     */
    private $priority;

    /**
     * @var resource|string
     */
    private $stream;

    /**
     * @var int
     */
    private $level;

    /**
     * @param null|resource|string $stream
     * @param null|mixed[] $channels
     * @param null|mixed[] $exceptChannels
     */
    public function __construct(
        $stream = null,
        ?int $level = null,
        ?array $channels = null,
        ?array $exceptChannels = null,
        ?int $priority = null,
    ) {
        $this->stream = $stream ?? 'php://stdout';
        $this->level = $level ?? Logger::DEBUG;
        $this->channels = $channels;
        $this->exceptChannels = $exceptChannels ?? ['event'];
        $this->priority = $priority;
    }

    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface>
     *
     * @throws \Exception
     */
    public function handlers(): iterable
    {
        $handlerConfig = HandlerConfig::create(new StreamHandler($this->stream, $this->level));
        $handlerConfig
            ->channels($this->channels)
            ->exceptChannels($this->exceptChannels)
            ->priority($this->priority);

        yield $handlerConfig;
    }
}
