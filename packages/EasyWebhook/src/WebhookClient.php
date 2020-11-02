<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Exceptions\InvalidWebhookUrlException;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultHandlerInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultHandlerInterface
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

    public function configure(WebhookInterface $webhook): WebhookInterface
    {
        if ($webhook->isConfigured()) {
            return $webhook;
        }

        foreach ($this->configurators as $configurator) {
            $configurator->configure($webhook);
        }

        return $webhook->configured(true);
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface[]
     */
    public function getConfigurators(): array
    {
        return $this->configurators;
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        $webhook = $this->configure($webhook);

        $method = $webhook->getMethod() ?? WebhookInterface::DEFAULT_METHOD;
        $url = $webhook->getUrl();

        if (empty($url)) {
            throw new InvalidWebhookUrlException('Webhook URL required');
        }

        try {
            $response = $this->httpClient->request($method, $url, $webhook->getHttpClientOptions() ?? []);
            // Trigger exception on bad response
            $response->getContent();

            $result = new WebhookResult($webhook, $response);
        } catch (\Throwable $throwable) {
            $response = null;

            if ($throwable instanceof HttpExceptionInterface) {
                $response = $throwable->getResponse();
            }

            $result = new WebhookResult($webhook, $response, $throwable);
        }

        return $this->resultHandler->handle($result);
    }

    /**
     * @param null|iterable<mixed> $configurators
     *
     * @return \EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface[]
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
                return $first->getPriority() <=> $second->getPriority();
            }
        );

        return $configurators;
    }
}
