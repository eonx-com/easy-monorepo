<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Client;

use EonX\EasyNotification\Configurator\QueueMessageConfiguratorInterface;
use EonX\EasyNotification\Enum\MessageStatus;
use EonX\EasyNotification\Exception\ApiRequestFailedException;
use EonX\EasyNotification\Exception\ConfigRequiredException;
use EonX\EasyNotification\Factory\QueueTransportFactoryInterface;
use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessage;
use EonX\EasyNotification\ValueObject\ConfigInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class NotificationClient implements NotificationClientInterface
{
    private ?ConfigInterface $config = null;

    /**
     * @var \EonX\EasyNotification\Configurator\QueueMessageConfiguratorInterface[]
     */
    private readonly array $configurators;

    private readonly HttpClientInterface $httpClient;

    public function __construct(
        iterable $configurators,
        private readonly QueueTransportFactoryInterface $transportFactory,
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();

        /** @var \EonX\EasyNotification\Configurator\QueueMessageConfiguratorInterface[] $filteredAndSortedConfigurators */
        $filteredAndSortedConfigurators = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($configurators, QueueMessageConfiguratorInterface::class)
        );
        $this->configurators = $filteredAndSortedConfigurators;
    }

    public function deleteMessage(string $messageId): void
    {
        if ($this->config === null) {
            throw new ConfigRequiredException(\sprintf('Config must be set before calling "%s"', __METHOD__));
        }

        $this->sendApiRequest('DELETE', \sprintf('messages/%s', $messageId));
    }

    /**
     * @param string[] $topics
     * @param array|null $options HTTP Client options
     */
    public function getMessages(array $topics, ?array $options = null): array
    {
        if ($this->config === null) {
            throw new ConfigRequiredException(\sprintf('Config must be set before calling "%s"', __METHOD__));
        }

        $options = \array_merge_recursive($options ?? [], [
            'query' => [
                'topic' => $topics,
            ],
        ]);

        return $this->sendApiRequest('GET', 'messages', $options);
    }

    public function send(MessageInterface $message): void
    {
        if ($this->config === null) {
            throw new ConfigRequiredException(\sprintf('Config must be set before calling "%s"', __METHOD__));
        }

        $queueMessage = new QueueMessage();

        foreach ($this->configurators as $configurator) {
            $queueMessage = $configurator->configure($this->config, $queueMessage, $message);
        }

        $this->transportFactory->create($this->config)
            ->send($queueMessage);
    }

    /**
     * @param string[] $messages Messages IDs
     */
    public function updateMessagesStatus(array $messages, MessageStatus $status): void
    {
        if ($this->config === null) {
            throw new ConfigRequiredException(\sprintf('Config must be set before calling "%s"', __METHOD__));
        }

        $this->sendApiRequest('PUT', 'messages', [
            'json' => [
                'messages' => $messages,
                'status' => $status->value,
            ],
        ]);
    }

    public function withConfig(?ConfigInterface $config = null): NotificationClientInterface
    {
        $this->config = $config;

        return $this;
    }

    private function sendApiRequest(string $method, string $path, ?array $options = null): array
    {
        if ($this->config === null) {
            throw new ConfigRequiredException(\sprintf('Config must be set before calling "%s"', $method));
        }

        $options = \array_merge($options ?? [], [
            'auth_basic' => [$this->config->getApiKey()],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        try {
            $response = $this->httpClient->request($method, $this->config->getApiUrl() . $path, $options);

            return $response->getContent() !== '' ? $response->toArray() : [];
        } catch (Throwable $throwable) {
            throw new ApiRequestFailedException(
                \sprintf('API Request failed: %s', $throwable->getMessage()),
                $throwable->getCode(),
                $throwable
            );
        }
    }
}
