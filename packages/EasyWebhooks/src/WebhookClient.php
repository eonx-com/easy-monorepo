<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks;

use EonX\EasyWebhooks\Exceptions\InvalidWebhookMethodException;
use EonX\EasyWebhooks\Exceptions\InvalidWebhookUrlException;
use EonX\EasyWebhooks\Interfaces\WebhookClientConfigInterface;
use EonX\EasyWebhooks\Interfaces\WebhookClientInterface;
use EonX\EasyWebhooks\Interfaces\WebhookConfiguratorInterface;
use EonX\EasyWebhooks\Interfaces\WebhookDataInterface;
use EonX\EasyWebhooks\Interfaces\WebhookInterface;
use EonX\EasyWebhooks\Interfaces\WebhookResultHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhooks\Interfaces\WebhookConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    /**
     * @var \EonX\EasyWebhooks\Interfaces\WebhookResultHandlerInterface
     */
    private $resultHandler;

    /**
     * @param null|iterable<mixed> $configurators
     */
    public function __construct(
        HttpClientInterface $httpClient,
        WebhookResultHandlerInterface $resultHandler,
        ?iterable $configurators = null
    ) {
        $this->httpClient = $httpClient;
        $this->resultHandler = $resultHandler;
        $this->configurators = $this->filterConfigurators($configurators);
    }

    public function sendWebhook(WebhookInterface $webhook): void
    {
        foreach ($this->configurators as $configurator) {
            $configurator->configure($webhook);
        }

        $method = $webhook->getMethod() ?? WebhookInterface::DEFAULT_METHOD;
        $url = $webhook->getUrl();

        if (empty($url)) {
            throw new InvalidWebhookUrlException('Webhook URL required');
        }

        try {
            $response = $this->httpClient->request($method, $url, $webhook->getHttpClientOptions() ?? []);
            $response->getContent(); // Trigger exception on bad response

            $this->resultHandler->handle(new WebhookResult($webhook, $response));
        } catch (\Throwable $throwable) {
            $response = null;

            if ($throwable instanceof HttpExceptionInterface) {
                $response = $throwable->getResponse();
            }

            $this->resultHandler->handle(new WebhookResult($webhook, $response, $throwable));
        }
    }

    /**
     * @param null|iterable<mixed> $configurators
     *
     * @return \EonX\EasyWebhooks\Interfaces\WebhookConfiguratorInterface[]
     */
    private function filterConfigurators(?iterable $configurators = null): array
    {
        if ($configurators === null) {
            return [];
        }

        $configurators = $configurators instanceof \Traversable
            ? \iterator_to_array($configurators)
            : (array)$configurators;

        $configurators = \array_filter($configurators, static function ($configurator): bool {
            return $configurator instanceof WebhookConfiguratorInterface;
        });

        \usort(
            $configurators,
            static function (WebhookConfiguratorInterface $first, WebhookConfiguratorInterface $second): int {
                return $second->getPriority() <=> $first->getPriority();
            }
        );

        return $configurators;
    }
}
