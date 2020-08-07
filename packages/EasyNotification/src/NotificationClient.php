<?php
declare(strict_types=1);

namespace EonX\EasyNotification;

use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Interfaces\NotificationClientInterface;
use EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface;
use EonX\EasyNotification\Interfaces\QueueTransportInterface;
use EonX\EasyNotification\Queue\QueueMessage;

final class NotificationClient implements NotificationClientInterface
{
    /**
     * @var \EonX\EasyNotification\Interfaces\ConfigInterface
     */
    private $config;

    /**
     * @var \EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \EonX\EasyNotification\Interfaces\QueueTransportInterface
     */
    private $transport;

    /**
     * @var iterable<mixed>
     */
    public function __construct(ConfigInterface $config, iterable $configurators, QueueTransportInterface $transport)
    {
        $this->config = $config;
        $this->configurators = $this->filterConfigurators($configurators);
        $this->transport = $transport;
    }

    public function send(MessageInterface $message): void
    {
        $queueMessage = new QueueMessage();

        foreach ($this->configurators as $configurator) {
            $queueMessage = $configurator->configure($this->config, $queueMessage, $message);
        }

        $this->transport->send($queueMessage);
    }

    /**
     * @param iterable<mixed> $configurators
     *
     * @return \EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface[]
     */
    private function filterConfigurators(iterable $configurators): array
    {
        $configurators = $configurators instanceof \Traversable
            ? \iterator_to_array($configurators)
            : (array)$configurators;

        $configurators = \array_filter($configurators, static function ($item): bool {
            return $item instanceof QueueMessageConfiguratorInterface;
        });

        \usort(
            $configurators,
            static function (QueueMessageConfiguratorInterface $first, QueueMessageConfiguratorInterface $second): int {
                return $first->getPriority() <=> $second->getPriority();
            }
        );

        return $configurators;
    }
}
