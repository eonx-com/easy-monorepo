<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Exceptions\InvalidWebhookUrlException;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\WebhookResult;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class SendWebhookMiddleware extends AbstractMiddleware
{
    public function __construct(
        private HttpClientInterface $httpClient,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $method = $webhook->getMethod() ?? WebhookInterface::DEFAULT_METHOD;
        $url = $webhook->getUrl() ?? '';

        if ($url === '') {
            throw new InvalidWebhookUrlException('Webhook URL required');
        }

        $throwable = null;

        try {
            $response = $this->httpClient->request($method, $url, $webhook->getHttpClientOptions() ?? []);
            // Trigger exception on bad response
            $response->getContent();
        } catch (Throwable $throwable) {
            // Set response to null here to make sure not to carry over the faulty response
            $response = null;

            if ($throwable instanceof HttpExceptionInterface) {
                $response = $throwable->getResponse();
            }
        }

        return new WebhookResult($webhook, $response, $throwable);
    }
}
