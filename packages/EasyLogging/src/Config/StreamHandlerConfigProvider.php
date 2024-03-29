<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class StreamHandlerConfigProvider implements HandlerConfigProviderInterface
{
    private int $level;

    /**
     * @var resource|string
     */
    private mixed $stream;

    /**
     * @param resource|string|null $stream
     * @param string[]|null $channels
     * @param string[]|null $exceptChannels
     */
    public function __construct(
        mixed $stream = null,
        ?int $level = null,
        private ?array $channels = null,
        private ?array $exceptChannels = null,
        private ?int $priority = null,
    ) {
        if ($stream !== null && \is_string($stream) === false && \is_resource($stream) === false) {
            throw new InvalidArgumentException(\sprintf(
                'Stream must be "null", "string" or "resource", "%s" given',
                \gettype($stream)
            ));
        }

        $this->stream = $stream ?? 'php://stdout';
        $this->level = $level ?? Logger::DEBUG;
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
            ->exceptChannels($this->exceptChannels ?? ['event'])
            ->priority($this->priority);

        yield $handlerConfig;
    }
}
