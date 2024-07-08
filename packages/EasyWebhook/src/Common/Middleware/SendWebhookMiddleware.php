<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\InvalidWebhookUrlException;
use EonX\EasyWebhook\Common\Exception\WebhookRequestFailedException;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
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

            if ($throwable instanceof ExceptionInterface) {
                $response = $throwable->getResponse();

                $throwable = new WebhookRequestFailedException(
                    message: 'Webhook request failed: ' . $throwable->getMessage(),
                    previous: $throwable
                );
            }
        }

        return new WebhookResult($webhook, $response, $throwable);
    }
}
